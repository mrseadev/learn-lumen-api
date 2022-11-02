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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/'], function () use ($router) {
    $router->post('users/add', 'UserController@add');
    $router->get('users/login', 'UserController@login');
    $router->get('users/refresh_token', ['middleware' => 'auth', 'uses' => 'UserController@refreshToken']);
    $router->get('users/{id}/posts', ['middleware' => 'auth', 'uses' => 'PostController@getUserPosts']);

    $router->group(['prefix' => 'posts/', 'middleware' => 'auth'], function () use ($router) {
        $router->get('', 'PostController@gets');
        $router->get('{id}', 'PostController@get');
        $router->post('add', 'PostController@add');
        $router->put('{id}/update', 'PostController@update');
        $router->post('{id}/duplicate', 'PostController@duplicate');
        $router->delete('{id}/delete', 'PostController@delete');
        $router->delete('delete', 'PostController@deleteAll');
    });
});
