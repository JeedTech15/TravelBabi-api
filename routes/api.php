<?php

use App\Http\Controllers\AbonnementAdminController;
use App\Http\Controllers\AbonnementUserController;
use App\Http\Controllers\AdminControlleur;
use App\Http\Controllers\AuthUserController;
use App\Http\Controllers\CheckPaiementStatutController;
use App\Http\Controllers\FaqsController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\LieuxUserController;
use App\Http\Controllers\NotificationAdminController;
use App\Http\Controllers\NotificationUserController;
use App\Http\Controllers\PackAdminController;
use App\Http\Controllers\PackUserController;
use App\Http\Controllers\PaiementAbonnementController;
use App\Http\Controllers\PaiementPackController;
use App\Http\Controllers\PubAdminController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/authentification/user', [AuthUserController::class, 'auth_user']);
Route::post('/verify/otp/user', [AuthUserController::class, 'verify_otp']);
Route::post('/renvoyer/otp/user', [AuthUserController::class, 'renvoyer_otp']);
Route::get('/info/user', [AuthUserController::class, 'info_user'])->middleware('auth:user');
Route::post('/update/info/user', [AuthUserController::class, 'update_info_user'])->middleware('auth:user');
Route::post('/update/device/token', [AuthUserController::class, 'update_device_token'])->middleware("auth:user");

//Packs
Route::get('/packs', [PackUserController::class, 'packs']);
Route::post('/buy/pack', [PaiementPackController::class, 'initialiser_paiement'])->middleware("auth:user");
// Route::post('/webhook/pack/genius', [PaiementPackController::class, 'handleWebhook']);
// Route::get('/payment/pack/success', [PaiementPackController::class, 'paymentSuccess']);
// Route::get('/payment/pack/error', [PaiementPackController::class, 'paymentError']);


//Abonnement
Route::get('/abonnements', [AbonnementUserController::class, 'abonnements']);
Route::post('/buy/abonnement', [PaiementAbonnementController::class, 'initialiser_paiement'])->middleware("auth:user");
// Route::post('/webhook/abonnement/genius', [PaiementAbonnementController::class, 'handleWebhook']);
// Route::get('/payment/abonnement/success', [PaiementAbonnementController::class, 'paymentSuccess']);
// Route::get('/payment/abonnement/error', [PaiementAbonnementController::class, 'paymentError']);

//Afficher le statut d’un paiement (Abonnement ou Pack)
Route::get('/payment/check/{reference}', [CheckPaiementStatutController::class, 'check_status']);

//Webhook de paiement 
Route::get('/webhook/paiement', [WebhookController::class, 'handleWebhook']);

//Recherche de lieu
Route::get('/search', [LieuxUserController::class, 'search_lieu']);

//Afficher la position actuelle de l’utilisateur
Route::get('/show/position', [LieuxUserController::class, 'positionUser']);
Route::get('/place/position', [LieuxUserController::class, 'positionUserFormatted']);

//Notifications
Route::get('/notifications', [NotificationUserController::class, 'notifications'])->middleware('auth:user');


Route::post('/login/admin', [AdminControlleur::class, 'login_admin']);
Route::post('/ajouter/admin', [AdminControlleur::class, 'add_admin'])->middleware('auth:admin');
Route::post('/update/info/admin', [AdminControlleur::class, 'update_admin_profile'])->middleware('auth:admin');
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
Route::post('/admin/update/password', [AdminControlleur::class, 'update_password_admin'])->middleware('auth:admin');
Route::post('/admin/forgot-password', [AdminControlleur::class, 'forgot']);
Route::get('/liste/user/admin', [AdminControlleur::class, 'liste_user_admin'])->middleware('auth:admin');
Route::get('/getById/user/admin/{id}', [AdminControlleur::class, 'getById_user_admin'])->middleware('auth:admin');
Route::post('/delete/user/admin/{id}', [AdminControlleur::class, 'delete_user_admin'])->middleware('auth:admin');
Route::post('/admin/verify-otp', [AdminControlleur::class, 'verifyOtpAdmin']);
Route::post('/notifications/all', [NotificationAdminController::class, 'notification_all'])->middleware('auth:admin');
Route::post('/notifications/multiple', [NotificationAdminController::class, 'notification_multiple'])->middleware('auth:admin');
Route::post('/ajouter/faqs/admin', [FaqsController::class, 'store_faqs'])->middleware('auth:admin');
Route::get('/liste/faqs/admin', [FaqsController::class, 'index_faqs']);
Route::post('/update/faqs/{id}', [FaqsController::class, 'update_faqs'])->middleware('auth:admin'); 
Route::post('/delete/faqs/{id}', [FaqsController::class, 'delete_faqs'])->middleware('auth:admin');
Route::post('/store/confidentialité/admin', [LegalDocumentController::class, 'strore_confidentilité'])->middleware('auth:admin');
Route::post('/update/confidentialite/admin/{id}', [LegalDocumentController::class, 'update_confidentialite'])->middleware('auth:admin');
Route::post('/delete/confidentialite/admin/{id}', [LegalDocumentController::class, 'delete_confidentialite'])->middleware('auth:admin');
Route::post('/legal-document/publish/{id}', [LegalDocumentController::class, 'publish'])->middleware('auth:admin');
Route::get('/legal-documents/{type}',[LegalDocumentController::class, 'getActive']);
