<?php

use App\Http\Controllers\AbonnementAdminController;
use App\Http\Controllers\AbonnementUserController;
use App\Http\Controllers\AdminControlleur;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\PackAdminController;
use App\Http\Controllers\PackUserController;
use App\Http\Controllers\PubAdminController;
use Illuminate\Support\Facades\Route;

Route::post('/authentification/user', [AuthUserController::class, 'auth_user']);
Route::post('/verify/otp/user', [AuthUserController::class, 'verify_otp']);
Route::post('/renvoyer/otp/user', [AuthUserController::class, 'renvoyer_otp']);
Route::get('/info/user', [AuthUserController::class, 'info_user'])->middleware('auth:user');
Route::post('/update/info/user', [AuthUserController::class, 'update_info_user'])->middleware('auth:user');

//Packs
Route::get('/packs', [PackUserController::class, 'packs']);
Route::post('/buy/pack', [PackUserController::class, 'buy_pack'])->middleware("auth:user");

//Abonnement
Route::get('/abonnements', [AbonnementUserController::class, 'abonnements']);

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
Route::post('/create/admin/pub', [PubAdminController::class, 'create_admin_pub'])->middleware('auth:admin');
Route::get('/liste/admin/pub', [PubAdminController::class, 'liste_admin_pubs'])->middleware('auth:admin');
Route::post('/update/admin/pub/{id}', [PubAdminController::class, 'update_admin_pub'])->middleware('auth:admin');
Route::post('/delete/admin/pub/{id}', [PubAdminController::class, 'delete_admin_pub'])->middleware('auth:admin');
