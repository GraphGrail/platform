<?php

namespace App\Http\Controllers\Domain;

use App\Domain\AiModel;
use App\Domain\Component;
use App\Domain\Configuration;
use App\Domain\Dataset\Dataset;
use App\Domain\Strategy\Strategy;
use App\Domain\Strategy\StrategyProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AiModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $models = AiModel::query()->where(['user_id' => Auth::id()])->get()->all();
        return view('domain/ai_models/index', ['models' => $models]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param StrategyProvider $provider
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(StrategyProvider $provider, Request $request)
    {
        if ($strategy = $request->get('strategy')) {
            /** @var Strategy $strategy */
            $strategy = $provider->get($strategy);

            return view('domain/ai_models/form', [
                    'model' => new AiModel(['user_id' => Auth::id()]),
                    'strategy' => $strategy,
                    'datasets' => Dataset::query()->where(['status' => Dataset::STATUS_READY])->get()->all(),
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'strategy' => 'required',
        ]);
        $model = new AiModel([
            'user_id' => Auth::id(),
            'status' => AiModel::STATUS_NEW,
        ]);
        $this->saveComponents($request, $model);

        return Redirect::to(\url('ai-models', ['model' => $model]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Domain\AiModel  $model
     * @return \Illuminate\Http\Response
     */
    public function show(AiModel $model)
    {
        return view('domain/ai_models/show', ['model' => $model]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Domain\AiModel $model
     * @return \Illuminate\Http\Response
     * @throws \App\Domain\Exception\ConfigurationException
     */
    public function edit(AiModel $model)
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
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Domain\AiModel $model
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function update(Request $request, AiModel $model)
    {
        $request->validate([
            'strategy' => 'required',
        ]);
        $this->saveComponents($request, $model);

        return Redirect::to(\url('ai-models', ['model' => $model]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Domain\AiModel  $model
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiModel $model)
    {
        try {
            $model->dataset->delete();
            $model->configuration->delete();
            $model->delete();
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
        return Redirect::to(\url('ai-models'));
    }

    public function train(AiModel $model, Request $request)
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

    public function stop(AiModel $model)
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

    public function exec(AiModel $model, Request $request)
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
        return view('domain/ai_models/show', ['model' => $model, 'result' => $result]);
    }

    /**
     * @param Request $request
     * @param AiModel $model
     * @return AiModelController
     * @throws ValidationException
     */
    private function saveComponents(Request $request, AiModel $model): AiModelController
    {
        /** @var StrategyProvider $provider */
        $provider = app()->make(StrategyProvider::class);
        $strategy = $provider->get($request->get('strategy'));
        $data = $request->get(\get_class($strategy));

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
}
