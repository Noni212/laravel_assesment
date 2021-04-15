<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

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

Route::middleware('auth:api')->post('/send_invitation', [UserController::class, 'sendUserEmail']);
Route::middleware('auth:api')->post('/verify_link', [UserController::class, 'verifyLink'])->name('verify_code');
Route::middleware('auth:api')->post('/update_profile', [UserController::class, 'updateProfile'])->name('updateProfile');
Route::post('register', [UserController::class, 'create'])->name("register");
Route::post('login', [UserController::class, 'login'])->name("user_login");


