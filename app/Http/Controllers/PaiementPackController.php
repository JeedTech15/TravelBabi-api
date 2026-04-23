<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Paiement;
use App\Models\PaiementPack;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaiementPackController extends Controller
{
    // ===============================
    // INIT PAIEMENT
    // ===============================
    public function initialiser_paiement(Request $request, NotificationService $notifService)
    {
        $validator = Validator::make($request->all(), [
            "id_pack" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            $user = $request->user();
            $pack = Pack::find($request->id_pack);

            if (!$pack) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pack introuvable'
                ], 404);
            }

            $paiementPack = PaiementPack::create([
                'id_user' => $user->id,
                'id_pack' => $pack->id,
                'prix' => $pack->prix,
                'statut' => 'pending'
            ]);

            Paiement::create([
                'utilisateur_id' => $user->id,
                'type' => "pack",
                'status_paiement' => 'pending',
                'montant' => $pack->prix,
            ]);

            $payload = [
                "amount" => $pack->prix,
                "description" => "Paiement de Pack " . $pack->libelle,
                "customer" => [
                    "name" => $user->nom,
                    "email" => $user->email,
                    "phone" => "+225" . $user->numero
                ],
                "success_url" => env('SUCCESS_PACK_URL'),
                "error_url" => env('ERROR_PACK_URL'),
                "metadata" => [
                    "paiement_id" => $paiementPack->id,
                    "user_id" => $user->id,
                    "pack_id" => $pack->id
                ]
            ];
            /** @var \Illuminate\Http\Client\Response $response */  
            $response = Http::withHeaders([
                'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
                'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
                'Content-Type' => 'application/json'
            ])->post(env('GENIUS_URL').'/payments', $payload);

            $result = $response->json();

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement rejeté',
                    'erreur' => $result['message'] ?? 'Erreur inconnue'
                ], 422);
            }

            // 🔥 STOCKAGE COMPLET
            $paiementPack->update([
                'statut' => 'pending',
                'data' => $result
            ]);

            if ($user->device_token) {
                $notifService->sendToUser(
                    $user,
                    "Paiement du pack {$pack->libelle} en cours ⏳",
                    "Votre paiement est en cours de traitement"
                );
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paiementPack->id,
                    'prix' => $paiementPack->prix,
                    'statut' => $paiementPack->statut,
                    'pack' => [
                        'id' => $pack->id,
                        'libelle' => $pack->libelle,
                        'nbr_etoile' => $pack->nbr_etoile,
                        'prix' => $pack->prix
                    ],
                    'redirect_url' => $result['data']['checkout_url'] ?? null
                ],
                'message' => 'Initialisation du paiement effectuée'
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (Throwable $e) {

            Log::error('Init paiement error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // WEBHOOK (SOURCE FIABLE)
    // ===============================
    public function handleWebhook(Request $request, NotificationService $notifService)
    {
        try {

            $signature = $request->header('X-Webhook-Signature');
            $timestamp = $request->header('X-Webhook-Timestamp');
            $event = $request->header('X-Webhook-Event');

            $payload = $request->all();

            $data = $timestamp . '.' . json_encode($payload);
            $secret = env('GENIUS_WEBHOOK_SECRET');

            if (!hash_equals(hash_hmac('sha256', $data, $secret), $signature)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            if (abs(time() - (int)$timestamp) > 300) {
                return response()->json(['error' => 'Expired timestamp'], 400);
            }

            $metadata = $payload['data']['metadata'] ?? null;

            if (!$metadata) return response()->json(['error' => 'Invalid metadata'], 400);

            $paiementPack = PaiementPack::find($metadata['paiement_id']);

            if (!$paiementPack) return response()->json(['error' => 'Not found'], 404);

            // 🔥 anti double traitement
            if (in_array($paiementPack->statut, ['completed', 'failed'])) {
                return response()->json(['success' => true]);
            }

            $newStatus = match ($event) {
                'payment.success' => 'completed',
                'payment.failed', 'payment.cancelled', 'payment.expired' => 'failed',
                default => null
            };

            if (!$newStatus) return response()->json(['success' => true]);

            $paiementPack->update([
                'statut' => $newStatus,
                'data' => $payload // 🔥 stockage webhook
            ]);

            $user = User::find($metadata['user_id']);

            if ($user && $user->device_token) {
                $notifService->sendToUser(
                    $user,
                    $newStatus === 'completed' ? "Paiement réussi ✅" : "Paiement échoué ❌",
                    $newStatus === 'completed'
                        ? "Votre pack est activé"
                        : "Votre paiement a échoué"
                );
            }

            return response()->json(['success' => true]);

        } catch (Throwable $e) {

            Log::error('Webhook error', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // ===============================
    // SUCCESS
    // ===============================
    public function paymentSuccess(Request $request, NotificationService $notifService)
    {
        return $this->handleRedirect($request, $notifService, 'completed');
    }

    // ===============================
    // ERROR
    // ===============================
    public function paymentError(Request $request, NotificationService $notifService)
    {
        return $this->handleRedirect($request, $notifService, 'failed');
    }

    // ===============================
    // LOGIQUE COMMUNE
    // ===============================
    private function handleRedirect(Request $request, NotificationService $notifService, $status)
    {
        try {

            $reference = $request->query('reference');

            if (!$reference) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reference manquante'
                ], 400);
            }
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
                'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
            ])->get(env('GENIUS_URL')."/payments/{$reference}");

            $result = $response->json();

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de récupérer le paiement'
                ], 400);
            }

            $metadata = $result['data']['metadata'] ?? null;

            if (!$metadata) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metadata introuvable'
                ], 400);
            }

            $paiementPack = PaiementPack::find($metadata['paiement_id']);

            if (!$paiementPack) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement introuvable'
                ], 404);
            }

            // 🔥 anti double traitement
            if (!in_array($paiementPack->statut, ['completed', 'failed'])) {
                $paiementPack->update([
                    'statut' => $status,
                    'data' => $result // 🔥 stockage complet
                ]);
            }

            $pack = Pack::find($metadata['pack_id']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paiementPack->id,
                    'prix' => $paiementPack->prix,
                    'statut' => $paiementPack->statut,
                    'pack' => [
                        'id' => $pack->id,
                        'libelle' => $pack->libelle,
                        'nbr_etoile' => $pack->nbr_etoile,
                        'prix' => $pack->prix
                    ],
                    'transaction' => $result
                ],
                'message' => $status === 'completed'
                    ? 'Paiement réussi'
                    : 'Paiement échoué'
            ]);

        } catch (Throwable $e) {

            Log::error('Redirect error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }
}