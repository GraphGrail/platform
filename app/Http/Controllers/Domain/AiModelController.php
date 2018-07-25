<?php

namespace App\Http\Controllers\Domain;

use App\Domain\AiModel;
use App\Domain\Configuration;
use App\Domain\Dataset\Dataset;
use App\Domain\Strategy\StrategyProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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
     * @return \Illuminate\Http\Response
     */
    public function create(StrategyProvider $provider)
    {
        return view('domain/ai_models/form', [
                'model' => new AiModel(['user_id' => Auth::id()]),
                'provider' => $provider,
                'datasets' => Dataset::query()->where(['status' => Dataset::STATUS_READY])->get()->all(),
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param StrategyProvider $provider
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request, StrategyProvider $provider)
    {
        $request->validate([
            'strategy' => 'required',
        ]);

        $strategy = $provider->get($request->get('strategy'));
        $datasetId = $request->get('dataset');

        $data = $request->get(\get_class($strategy));

        foreach ($strategy->getComponents() as $component) {
            $component->validate($data[\get_class($component)]);
        }

        $config = new Configuration([
            'user_id' => Auth::id(),
            'strategy_class' => $request['strategy'],
        ]);
        if (!$config->save()) {
            throw new RuntimeException('Configuration not saved');
        }
        foreach ($strategy->getComponents() as $component) {
            $class = \get_class($component);
            $link = new Configuration\ComponentRelation([
                'component_class' => $class,
                'component_attributes' => $data[$class],
            ]);
            $config->componentRelations()->save($link);
        }

        $model = new AiModel([
            'user_id' => Auth::id(),
            'status' => AiModel::STATUS_NEW,
            'dataset_id' => $datasetId,
            'configuration_id' => $config->id,
        ]);
        if (!$model->save()) {
            throw new RuntimeException('Configuration not saved');
        }
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
     * @param  \App\Domain\AiModel  $model
     * @return \Illuminate\Http\Response
     */
    public function edit(AiModel $model, StrategyProvider $provider)
    {
        return view('domain/ai_models/form', [
                'model' => $model,
                'provider' => $provider,
                'datasets' => Dataset::query()->where(['status' => Dataset::STATUS_READY])->get()->all(),
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Domain\AiModel  $model
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiModel $model, StrategyProvider $provider)
    {
        $request->validate([
            'strategy' => 'required',
        ]);

        $strategy = $provider->get($request->get('strategy'));
        $datasetId = $request->get('dataset');

        $data = $request->get(\get_class($strategy));

        foreach ($strategy->getComponents() as $component) {
            $component->validate($data[\get_class($component)]);
        }

        $config = $model->configuration;
        foreach ($strategy->getComponents() as $component) {
            $class = \get_class($component);
            $link = new Configuration\ComponentRelation([
                'component_class' => $class,
                'component_attributes' => $data[$class],
            ]);
            $config->componentRelations()->save($link);
        }

        $model->dataset_id = $datasetId;
        if (!$model->save()) {
            throw new RuntimeException('Configuration not saved');
        }
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
        $strategy->exec($model);
        return Redirect::to(\url('ai-models', ['model' => $model]));
    }
}
