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
use \App\Http\Middleware\ApiAuthMiddleware;


// Index route
Route::get('/', function () {
    return view('welcome');
});

Route::get('/contact', function () {
    return 'Hello world with Laravel';
});


Route::get('/pruebas/{name?}', function($name = null){

	return view("test", array(
		"name" => $name
	));
});

Route::get('/test-orm', 'PruebasController@testOrm');

// API ROUTES
	
	

	// Controlador de usuario
	Route::post('/api/register', 'UserController@register');
	Route::post('/api/login', 'UserController@login');

	Route::put('/api/user/update', 'UserController@update');

	Route::post('/api/user/upload','UserController@upload')->middleware(ApiAuthMiddleware::class);
	Route::get('/api/user/avatar/{filename}' , 'UserController@getImage');
	Route::get('/api/user/detail/{id}', 'UserController@userDetail');

	// Controlador de categorias
	Route::resource('/api/category', 'CategoryController');

	// Rutas del controlador de entradas / posts
	Route::resource('/api/post', 'PostController');
	Route::post('/api/post/upload','PostController@upload');
	Route::get('/api/post/image/{filename}', 'PostController@getImage');
	Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
	Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');


