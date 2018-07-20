<?php

namespace App\Http\Controllers\Domain;

use App\Domain\AiModel;
use App\Domain\Strategy\StrategyProvider;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

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
     * @return \Illuminate\Http\Response
     */
    public function create(StrategyProvider $provider)
    {
        return view('domain/ai_models/form', [
                'model' => new AiModel(['user_id' => Auth::id()]),
                'provider' => $provider,
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {




        return Redirect::to('ai-models');
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
    public function edit(AiModel $model)
    {
        return view('domain/ai_models/form', ['model' => $model]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Domain\AiModel  $model
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AiModel $model)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Domain\AiModel  $model
     * @return \Illuminate\Http\Response
     */
    public function destroy(AiModel $model)
    {
        //
    }
}
