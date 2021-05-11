<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RulesController;
use Illuminate\Support\Facades\Route;

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


Route::get('login', [LoginController::class, 'redirectToProvider'])->name('login');
Route::get('callback', [LoginController::class, 'handleProviderCallback']);
Route::post('logout', [LoginController::class, 'logout']);

Route::get('/', [RulesController::class, 'index']);
Route::post('updateRules', [RulesController::class, 'updateRules']);
Route::get('activities', [RulesController::class, 'activities']);
Route::get('allRules', [RulesController::class, 'allRules']);
