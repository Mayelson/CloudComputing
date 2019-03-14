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


Route::get('voters/name/{params}', 'Controller@votersByName');
Route::get('sections', 'Controller@votersSections');
Route::get('voters/voter_number/{id}', 'Controller@voterNumber');

Route::get('voters/', 'Controller@voter');
Route::patch('/voters/{id}/vote', 'Controller@vote');
Route::patch('/voters/reset', 'Controller@voteReset');
Route::get('voters/{id}', 'Controller@votersById');
Route::get('voters/sections/{id}', 'Controller@votersBySection');

