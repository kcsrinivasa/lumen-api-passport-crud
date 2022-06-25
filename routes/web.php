<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


$router->group(['prefix'=>'api'],function() use($router){
    /* un authenticated routes */
    $router->post('/login','AuthController@login');
    $router->post('/register','AuthController@register');
    $router->post('/forgot','AuthController@forgot');
    $router->post('/reset','AuthController@reset');
});

$router->group(['prefix'=>'api','middleware'=>'auth'],function() use($router){
    /* authenticated routes */
    $router->post('/logout','AuthController@logout');
    /* crud post routes*/
    $router->get('/posts','PostController@index');
    $router->get('/posts/{id}','PostController@show');
    $router->post('/posts','PostController@store');
    $router->put('/posts/{id}','PostController@update');
    $router->delete('/posts/{id}','PostController@destroy');
});