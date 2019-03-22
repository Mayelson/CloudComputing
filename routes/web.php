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


use Illuminate\Http\Request;

Route::get('voters/name', 'Controller@votersByName');
Route::get('sections', 'Controller@votersSections');
Route::get('voters/voter_number/{id}', 'Controller@voterNumber');

Route::get('voters/', 'Controller@voter');
Route::patch('/voters/{id}/vote', 'Controller@vote');
Route::patch('/voters/reset', 'Controller@voteReset');
Route::get('voters/{id}', 'Controller@votersById');
Route::get('voters/sections/{id}', 'Controller@votersBySection');


