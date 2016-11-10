<?php
// These routes are localhost ONLY.
Route::get('/qlitics.js', "ProxyController@proxyGet");
Route::get('/api/{route}', "ProxyController@proxyGet")->where('route', '.*');
Route::post('/api/{route}', "ProxyController@proxyPost")->where('route', '.*');

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');

Route::get('/ping', function () {
    return 'pong';
});

Route::get('/preview/home', 'PreviewController@home');

Route::get('/preview/story', 'PreviewController@story');

Route::get('/section/{section}', 'HomeController@section');

Route::get('/{category}/{y}/{m}/{d}/{slug}', 'HomeController@story');

Route::get('/author/{authorId}', 'HomeController@author');

Route::get('/tag', 'HomeController@tag');

Route::get('/search', 'HomeController@search');
