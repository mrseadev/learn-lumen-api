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

$router->post('/newuser', 'UserController@createNewUser');

$router->get('/users/{id}/posts', 'PostController@getUserPosts');

$router->group(['prefix' => 'posts'], function () use ($router) {
    $router->get('/', 'PostController@gets');
    $router->get('/{id}', 'PostController@get');
    $router->post('/add', 'PostController@add');
    $router->put('/{id}/update', 'PostController@update');
    $router->post('/{id}/duplicate', 'PostController@duplicate');
    $router->delete('/delete', 'PostController@deleteAll');
    $router->delete('/{id}/delete', 'PostController@delete');
});
