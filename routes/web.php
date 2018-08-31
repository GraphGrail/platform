<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (!\Illuminate\Support\Facades\Auth::id()) {
        return redirect()->route('login');
    }
    return redirect()->route('home');
});

Auth::routes();


Route::middleware('auth')->group(function () {
    Route::get('/', 'HomeController@index')->name('home');

    Route::get('/datasets/download/{dataset}', 'Domain\DatasetController@download')->name('datasets.download');
    Route::resource('datasets', 'Domain\DatasetController');

    Route::get('labels', 'Domain\LabelController@index');
    Route::get('labels/{group}', 'Domain\LabelController@show');
    Route::get('/labels/json/{dataset}', 'Domain\LabelController@json');

    Route::post('ai-models/train/{model}', 'Domain\AiModelController@train', ['parameters' => ['ai-models' => 'model']]);
    Route::post('ai-models/stop/{model}', 'Domain\AiModelController@stop', ['parameters' => ['ai-models' => 'model']]);
    Route::post('ai-models/exec/{model}', 'Domain\AiModelController@exec', ['parameters' => ['ai-models' => 'model']])->name('ai-models.exec');
    Route::get('ai-models/status/{model}', 'Domain\AiModelController@status', ['parameters' => ['ai-models' => 'model']])->name('ai-models.status');
    Route::resource('ai-models', 'Domain\AiModelController', ['parameters' => ['ai-models' => 'model']]);
});
