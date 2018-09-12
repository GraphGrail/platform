<?php

namespace App\Http\Controllers\Domain;

use App\Domain\AiModel;
use App\Domain\Component;
use App\Domain\Configuration;
use App\Domain\Dataset\Dataset;
use App\Domain\Exception\ConfigurationException;
use App\Domain\Strategy\Strategy;
use App\Domain\Strategy\StrategyProvider;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class AiModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $models = AiModel::query()->where(['user_id' => Auth::id()])->get()->all();
        return view('domain/ai_models/index', ['models' => $models]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param StrategyProvider $provider
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|View
     */
    public function create(StrategyProvider $provider, Request $request): View
    {

        /** @var User $user */
        $user = Auth::user();
        $strategy = $user->isNew ? \App\Domain\Strategy\iPavlov\Strategy::class : null;

        if ($strategy = $strategy ?? $request->get('strategy')) {
            /** @var Strategy $strategy */
            $strategy = $provider->get($strategy);

            $model = new AiModel(['user_id' => Auth::id()]);
            $model->configuration = $strategy->createDefaultConfiguration();

            return view('domain/ai_models/form', [
                    'model' => $model,
                    'strategy' => $strategy,
                    'datasets' =>
                        Dataset::query()
                             ->where([
                                 'status' => Dataset::STATUS_READY,
                             ])
                             ->whereIn('user_id', [0, Auth::id()])
                             ->get()->all(),
                ]
            );
        }

        return view('domain/ai_models/strategies', [
                'provider' => $provider,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        \Validator::make(
            $request->all(),
            [
                'strategy' => 'required',
                'name' => 'required|string|max:255',
            ]
        )->validate();

        $model = new AiModel([
            'user_id' => Auth::id(),
            'status' => AiModel::STATUS_NEW,
        ]);
        $this->fillModel($request, $model);

        /** @var User $user */
        $user = Auth::user();
        if ($user->isNew) {
            $user->isNew = false;
            $user->save();
        }

        return Redirect::to(\url('ai-models', ['model' => $model]));
    }

    /**
     * Display the specified resource.
     *
     * @param  AiModel $model
     * @return View
     */
    public function show(AiModel $model): View
    {
        return view('domain/ai_models/show', ['model' => $model]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  AiModel $model
     * @return View
     * @throws ConfigurationException
     */
    public function edit(AiModel $model): View
    {
        return view('domain/ai_models/form', [
                'model' => $model,
                'strategy' => $model->configuration->strategy(),
                'datasets' => Dataset::query()->where(['status' => Dataset::STATUS_READY])->get()->all(),
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  AiModel $model
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request, AiModel $model): RedirectResponse
    {
        \Validator::make(
            $request->all(),
            [
                'strategy' => 'required',
                'name' => 'required|string|max:255',
            ]
        )->validate();

        $this->fillModel($request, $model);

        return Redirect::to(\url('ai-models', ['model' => $model]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AiModel  $model
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiModel $model): \Illuminate\Http\Response
    {
        try {
            if ($model->configuration) {
                $model->configuration->delete();
            }
            $model->delete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
        return Response::make();
    }

    /**
     * @param AiModel $model
     * @param Request $request
     * @return RedirectResponse
     * @throws ConfigurationException
     */
    public function train(AiModel $model, Request $request): RedirectResponse
    {
        if (!$strategy = $model->configuration->strategy()) {
            throw new RuntimeException('Strategy not set');
        }
        /** @var Dataset $dataset */
        if (!$dataset = Dataset::query()->find($request->get('dataset')) ) {
            throw new RuntimeException('Empty dataset');
        }
        if ($model->status >= AiModel::STATUS_READY) {
            throw new RuntimeException('The model is already trained');
        }
        $strategy->train($model, $dataset);
        return Redirect::to(\url('ai-models', ['model' => $model]));
    }

    /**
     * @param AiModel $model
     * @return RedirectResponse
     * @throws ConfigurationException
     */
    public function stop(AiModel $model): RedirectResponse
    {
        if (!$strategy = $model->configuration->strategy()) {
            throw new RuntimeException('Strategy not set');
        }
        if ($model->status >= AiModel::STATUS_TRAINED) {
            throw new RuntimeException('The model is already trained');
        }
        $strategy->stop($model);
        return Redirect::to(\url('ai-models', ['model' => $model]));
    }

    /**
     * @param AiModel $model
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|View
     * @throws ConfigurationException
     * @throws ValidationException
     */
    public function exec(AiModel $model, Request $request): View
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = \Validator::make($model->toArray(), [
            'status' => ['required', Rule::in([AiModel::STATUS_READY])],
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if (!$strategy = $model->configuration->strategy()) {
            throw new RuntimeException('Strategy not set');
        }
        $result = $strategy->exec($model, $request->get('api_query'));
        return view('domain/ai_models/show', ['model' => $model, 'result' => json_decode($result->getData(), true)]);
    }

    /**
     * @param Request $request
     * @param AiModel $model
     * @return AiModelController
     * @throws ValidationException
     */
    private function fillModel(Request $request, AiModel $model): AiModelController
    {
        /** @var StrategyProvider $provider */
        $provider = app()->make(StrategyProvider::class);
        $strategy = $provider->get($request->get('strategy'));
        $data = $request->get(\get_class($strategy));

        $model->name = $request->get('name');

        $selected = array_keys($data);

        /** @var Component[] $components */
        $components =
            collect($strategy->getComponents())
                ->filter(function (Component $component) use ($selected) {
                    return \in_array($component::name(), $selected, true);
                })
                ->sortBy(function (Component $component) use ($selected) {
                    return array_search($component::name(), $selected, true);
                })->all()
        ;

        $strategy->validate($components, $data);

        $config = $model->configuration ?? new Configuration([
                'user_id' => Auth::id(),
                'strategy_class' => $request['strategy'],
            ]);
        $config->save();

        $config->componentRelations()->delete();
        foreach ($components as $component) {
            $class = \get_class($component);
            $link = new Configuration\ComponentRelation([
                'component_class' => $class,
            ]);
            $link->component_attributes = $data[$component::name()];
            $config->componentRelations()->save($link);
        }

        $model->status = AiModel::STATUS_NEW;
        $model->errors = null;
        $model->configuration()->associate($config);
        if (!$model->save()) {
            throw new RuntimeException('Configuration not saved');
        }
        if ($request->get('dataset')) {
            $dataset = Dataset::query()->findOrFail($request->get('dataset'));
            $model->dataset()->associate($dataset)->save();
        }

        $strategy->verification($model);

        return $this;
    }

    public function status(AiModel $model): int
    {
        return $model->status;
    }
}
