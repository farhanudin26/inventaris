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

//stuff
//struktur : $router->method('/path', 'NamaController@namaFunction');
//statis
$router->get('stuffs', 'StuffController@index');
$router->post('/stuffs/store', 'StuffController@store');
$router->get('/stuffs/trash', 'StuffController@trash');
//dinamin
$router->get('/stuffs/{id}', 'StuffController@show');
$router->patch('/stuffs/update/{id}', 'StuffController@update');
$router->delete('/stuffs/delete/{id}', 'StuffController@destroy');
//softdeletes : trash, restore, undo
$router->get('/stuffs/trash/restore/{id}', 'StuffController@restore');
$router->get('/stuffs/trash/permanent-delete/{id}', 'StuffController@permanenDelete');

//inbound
$router->post('/inbound-stuffs/store','InboundStuffController@store');
$router->post('/inbound-stuffs/delete/{id}','InboundStuffController@destroy');


//user
//statis
$router->get('users', 'UserController@index');
$router->post('/users/store', 'UserController@store');
$router->get('/users/trash', 'UserController@trash');
//dinamis
$router->get('/users/{id}', 'UserController@show');
$router->patch('/users/update/{id}', 'UserController@update');
$router->delete('/users/delete/{id}', 'UserController@destroy');
$router->get('/users/trash/restore/{id}', 'UserController@restore');
$router->get('/users/trash/permanent-delete/{id}', ' UserController@permanenDelete');


