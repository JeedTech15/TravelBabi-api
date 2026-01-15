<?php

use App\Http\Controllers\AuthUserController;
use Illuminate\Support\Facades\Route;

Route::post('/authentification/user', [AuthUserController::class, 'auth_user']);
Route::post('/verify/otp/user', [AuthUserController::class, 'verify_otp']);
Route::post('/renvoyer/otp/user', [AuthUserController::class, 'renvoyer_otp']);
Route::get('/info/user', [AuthUserController::class, 'info_user'])->middleware('auth:user');
Route::post('/update/info/user', [AuthUserController::class, 'update_info_user'])->middleware('auth:user');