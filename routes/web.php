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
    return view('welcome');
});

Auth::routes();


Route::middleware('auth')->group(function () {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/datasets/download/{dataset}', 'Domain\DatasetController@download')->name('datasets.download');
    Route::resource('datasets', 'Domain\DatasetController');

    Route::get('labels', 'Domain\LabelController@index');
    Route::get('labels/{group}', 'Domain\LabelController@show');

    Route::post('ai-models/train/{model}', 'Domain\AiModelController@train', ['parameters' => ['ai-models' => 'model']]);
    Route::resource('ai-models', 'Domain\AiModelController', ['parameters' => ['ai-models' => 'model']]);
});
