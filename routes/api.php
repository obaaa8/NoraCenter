<?php

use Illuminate\Http\Request;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'prefix' => 'v1/auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', "AuthController@user"); #app/Http/Controllers/API/v1/AuthController.php
        Route::get('user/groups', 'ApiGroupsController@getUserGroups');
        Route::post('add_trainees', 'ApiAddTraineesController@addTrainees');
        Route::get('current_groups', 'ApiGroupsController@current_groups');
        Route::post('add_contact', 'Website@addContact');
        Route::post('join_to_group', 'ApiGroupsController@joinToGroup');
        Route::get('user/accounts', 'ApiAccountsController@userAccounts');
        Route::post('left_to_group', 'ApiGroupsController@leftToGroup');
    });
});

Route::group([
    'namespace' => 'Auth',
    'middleware' => 'api',
    'prefix' => 'v1/password'
], function () {
    Route::post('create', 'PasswordResetController@create');
    Route::get('find/{token}', 'PasswordResetController@find');
    Route::post('reset', 'PasswordResetController@reset');
});
