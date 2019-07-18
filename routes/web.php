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
//    return view('welcome');
    return redirect('/home');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::middleware(['auth'])->group(function (){

    Route::get('/prediction/map', 'PredictionController@predictionMap')->name('getPredictionMap');
    Route::post('/prediction/map', 'PredictionController@getPredictionForWeek')->name('predictionOfWeek');

    Route::get('/comparison/map', 'PredictionController@comparisonMap')->name('getComparisonMap');
    Route::post('/realdata/map', 'PredictionController@getRealDataForWeek')->name('realDataOfWeek');

    Route::get('/comparison/chart', 'PredictionController@comparisonchart')->name('getComparisonChart');
    Route::post('/comparison/chart', 'PredictionController@getComparisonDataforMoh')->name('getComparisonChartData');

});
