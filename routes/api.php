<?php

use App\Http\Controllers\AbonnementAdminController;
use App\Http\Controllers\AdminControlleur;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\PackAdminController;
use Illuminate\Support\Facades\Route;

Route::post('/authentification/user', [AuthUserController::class, 'auth_user']);
Route::post('/verify/otp/user', [AuthUserController::class, 'verify_otp']);
Route::post('/renvoyer/otp/user', [AuthUserController::class, 'renvoyer_otp']);
Route::get('/info/user', [AuthUserController::class, 'info_user'])->middleware('auth:user');
Route::post('/update/info/user', [AuthUserController::class, 'update_info_user'])->middleware('auth:user');
Route::post('/login/admin', [AdminControlleur::class, 'login_admin']);
Route::post('/ajouter/admin', [AdminControlleur::class, 'add_admin'])->middleware('auth:admin');
Route::post('/update/info/admin', [AdminControlleur::class, 'update_profil_admin'])->middleware('auth:admin');
Route::post('/delete/admin/{id}', [AdminControlleur::class, 'delete_admin'])->middleware('auth:admin');
Route::get('/liste/admins', [AdminControlleur::class, 'liste_admin'])->middleware('auth:admin');
Route::post('/create/pack/admin', [PackAdminController::class, 'create_pack'])->middleware('auth:admin');
Route::get('/liste/pack/admin', [PackAdminController::class, 'liste_pack_admin'])->middleware('auth:admin');
Route::post('/update/pack/admin/{id}', [PackAdminController::class, 'update_pack_admin'])->middleware('auth:admin');
Route::post('/delete/pack/admin/{id}', [PackAdminController::class, 'delete_pack_admin'])->middleware('auth:admin');
Route::post('/create/admin/abonnement', [AbonnementAdminController::class, 'create_admin_admin'])->middleware('auth:admin');
Route::get('/liste/admin/abonnement', [AbonnementAdminController::class, 'liste_admin_abonnement'])->middleware('auth:admin');
Route::post('/update/admin/abonnement/{id}', [AbonnementAdminController::class, 'upadta_admin_abonnement'])->middleware('auth:admin');
Route::post('/delete/admin/abonnement/{id}', [AbonnementAdminController::class, 'delete_admin_abonnement'])->middleware('auth:admin');
