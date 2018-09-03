<?php

namespace App\Http\Controllers;

use App\Domain\AiModel;
use App\Domain\Dataset\Dataset;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

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
        $system = Dataset::query()->where(['user_id' => 0])->get()->all();
        $models = AiModel::query()->where(['user_id' => Auth::id()])->get()->all();

        return view('home', [
            'datasets' => $datasets,
            'system' => $system,
            'models' => $models,
        ]);
    }

    public function locale(Request $request)
    {
        $this->validate($request, [
            'locale' => Rule::in(['ru', 'en']),
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->locale = $request->get('locale');
        $user->save();

        return Redirect::back();
    }
}
