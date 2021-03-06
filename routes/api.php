<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/login', 'AuthController@login');
Route::post('/admin/login', 'AuthController@loginAdmin');
Route::post('/pusher/auth', 'AuthController@authPusher');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('order', 'Mobile\OrderController@create');
    Route::post('request', 'Mobile\RequestController@create');
    Route::get('/me/request', 'Mobile\MeController@getSendedRequests');
    Route::get('/me/request/{id}', 'Mobile\RequestController@showRequest');
    Route::get('/me/product', "Mobile\MeController@getProducts");
    Route::get('/me/request/{id}/product', 'Mobile\RequestController@getProducts');
    Route::get('/me/category', "Mobile\MeController@getCategories");
    Route::get('/me/product-order', "Mobile\MeController@getProductOrders");
    Route::get('/me/product-stock', "Mobile\MeController@getStockOrders");
    Route::post('/me/request/{id}/finish', 'Mobile\RequestController@requestFinish');
    Route::post('/logout', 'AuthController@logout');
});

Route::group(['middleware' => 'auth:api-admin'], function () {
    Route::resource('category', 'CategoryController', ['except' => [
        'create', 'edit'
    ]]);
    Route::resource('product', 'ProductController', ['except' => [
        'create', 'edit', 'update'
    ]]);
    Route::resource('seller', 'SellerController', ['except' => [
        'create', 'edit'
    ]]);

    Route::post('product/{id}/update', 'ProductController@update');
    Route::post('seller/{sellerId}/product/{productId}', 'ProductController@updateQuantity');
    Route::get('seller/{id}/product', 'SellerController@getProducts');
    Route::get('seller/{id}/order', 'SellerController@getOrders');
    Route::get('seller/{id}/request', 'SellerController@getRequests');
    Route::get('request', 'RequestController@index');
    Route::post('request/{id}/sent', 'RequestController@requestSent');
    Route::get('request/{id}', 'RequestController@show');
    Route::get('request/{id}/product', 'RequestController@getProducts');
    Route::get('order/{id}', 'OrderController@show');
    Route::get('order/{id}/product', 'OrderController@getProducts');
});