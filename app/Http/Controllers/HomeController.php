<?php

namespace App\Http\Controllers;

use App\Domain\AiModel;
use App\Domain\Dataset\Dataset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datasets = Dataset::query()->where(['user_id' => Auth::id()])->get()->all();
        $models = AiModel::query()->where(['user_id' => Auth::id()])->get()->all();

        return view('home', [
            'datasets' => $datasets,
            'models' => $models,
        ]);
    }
}
