<?php

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

define("API_VERSION", "v1");
define("API_PREFIX", "api/" . API_VERSION);

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => API_PREFIX . "/users"], function () use ($router) {
    $router->post('/add', 'UserController@add');
    $router->get('/login', 'UserController@login');
    $router->get('/refresh_token', ['middleware' => 'auth', 'uses' => 'UserController@refreshToken']);
    $router->get('/{id}/posts', ['middleware' => 'auth', 'uses' => 'PostController@getUserPosts']);
});

$router->group(['prefix' => API_PREFIX . "/posts", 'middleware' => 'auth'], function () use ($router) {
    $router->get('', 'PostController@gets');
    $router->get('/{id}', 'PostController@get');
    $router->post('/add', 'PostController@add');
    $router->put('/{id}/update', 'PostController@update');
    $router->post('/{id}/duplicate', 'PostController@duplicate');
    $router->delete('/{id}/delete', 'PostController@delete');
    $router->delete('/delete', 'PostController@deleteAll');
    $router->post('/add_with_transaction', 'PostController@addWithTransaction');
});

$router->group(['prefix' => API_PREFIX . "/todos", 'middleware' => 'auth'], function () use ($router) {
    $router->get('', 'TodoController@index');
    $router->get('/{id}', 'TodoController@get');
    $router->post('/add', 'TodoController@add');
    $router->put('/{id}/update', 'TodoController@update');
    $router->post('/{id}/duplicate', 'TodoController@duplicate');
    $router->delete('/{id}/delete', 'TodoController@delete');
    $router->delete('/delete', 'TodoController@deleteAll');
});
