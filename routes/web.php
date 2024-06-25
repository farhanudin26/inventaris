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

//cors
$router->group(['middleware' => 'cors'], function ($router) {

//static
//struktur : $router->method('/path', 'NamaController@namaFunction');

//index
$router->get('stuffs', 'StuffController@index');
$router->get('users', 'UserController@index');
$router->get('lendings', 'LendingController@index');
$router->get('/inbound-stuffs', 'InboundStuffController@index');


//store
$router->post('/stuffs/store', 'StuffController@store');
$router->post('/users/store', 'UserController@store');
$router->post('/inbound-stuffs/store', 'InboundStuffController@store');
$router->post('/lendings/store', 'LendingController@store');
    //buat data restoration (pengembalian) menggunakan params data lending_id agar data pengembalian dibuat berdasarkan data peminjamannnya
    //lending_id data dinamis , agar pengembalian tertaut pada data peminjamannya
$router->post('/restorations/{lending_id}', 'RestorationController@store');

//trash
$router->get('/stuffs/trash', 'StuffController@trash');
$router->get('/users/trash', 'UserController@trash');
$router->get('/inbound-stuffs/trash', 'InboundStuffController@trash');

//dinamis

//show
$router->get('/stuffs/{id}', 'StuffController@show');
$router->get('/users/{id}', 'UserController@show');
$router->get('/inbound-stuffs/{id}', 'InboundStuffController@show');

//update
$router->patch('/users/update/{id}', 'UserController@update');
$router->patch('/stuffs/update/{id}', 'StuffController@update');

//destroy
$router->delete('/stuffs/delete/{id}', 'StuffController@destroy');
$router->delete('/users/delete/{id}', 'UserController@destroy');
$router->delete('/inbound-stuffs/delete/{id}', 'InboundStuffController@destroy');
$router->delete('/lendings/delete/{id}', 'LendingController@destroy');

//restore
$router->get('/stuffs/trash/restore/{id}', 'StuffController@restore');
$router->get('/users/trash/restore/{id}', 'UserController@restore');
$router->get('/restore/{id}', 'InboundStuffController@restore');

//permanent-delete
$router->get('/stuffs/trash/permanent-delete/{id}', 'StuffController@permanentDelete');
$router->get('/users/trash/permanent-delete/{id}', ' UserController@permanentDelete');
$router->delete('/inbound-stuffs/permanent-delete/{id}', 'InboundStuffController@permanentDelete');

//Auth'
$router->post('login', 'AuthController@login');
$router->get('logout', 'AuthController@logout');
$router->get('profile', 'AuthController@me');


});
