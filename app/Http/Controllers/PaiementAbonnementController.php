<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Paiement;
use App\Models\PaiementAbonnement;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PaiementAbonnementController extends Controller
{
    // ===============================
    // INIT PAIEMENT ABONNEMENT
    // ===============================
    public function initialiser_paiement(Request $request, NotificationService $notifService)
    {
        $validator = Validator::make($request->all(), [
            "id_abonnement" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            $user = $request->user();

            $abonnement = Abonnement::find($request->id_abonnement);

            if (!$abonnement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Abonnement introuvable'
                ], 404);
            }

            // création paiement abonnement
            $paiementAbonnement = PaiementAbonnement::create([
                'id_user' => $user->id,
                'id_abonnement' => $abonnement->id,
                'prix' => $abonnement->prix,
                'statut' => 'pending'
            ]);

            Paiement::create([
                'utilisateur_id' => $user->id,
                'type' => "abonnement",
                'status_paiement' => 'pending',
                'montant' => $abonnement->prix,
            ]);

            $payload = [
                "amount" => $abonnement->prix,
                "description" => "Abonnement " . $abonnement->libelle,
                "customer" => [
                    "name" => $user->nom,
                    "email" => $user->email,
                    "phone" => "+225" . $user->numero
                ],
                "success_url" => env('SUCCESS_URL_ABONNEMENT'),
                "error_url" => env('ERROR_URL_ABONNEMENT'),
                "metadata" => [
                    "paiement_id" => $paiementAbonnement->id,
                    "user_id" => $user->id,
                    "abonnement_id" => $abonnement->id
                ]
            ];

            $response = Http::withHeaders([
                'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
                'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
                'Content-Type' => 'application/json'
            ])->post(env('GENIUS_URL') . '/payments', $payload);

            $result = $response->json();

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement rejeté',
                    'erreur' => $result['message'] ?? 'Erreur inconnue'
                ], 422);
            }

            // 🔥 stockage complet API Genius
            $paiementAbonnement->update([
                'statut' => 'pending',
                'data' => $result
            ]);

            if ($user->device_token) {
                $notifService->sendToUser(
                    $user,
                    "Paiement abonnement {$abonnement->libelle} en cours ⏳",
                    "Votre abonnement est en cours de traitement"
                );
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paiementAbonnement->id,
                    'prix' => $paiementAbonnement->prix,
                    'statut' => $paiementAbonnement->statut,
                    'abonnement' => [
                        'id' => $abonnement->id,
                        'libelle' => $abonnement->libelle,
                        'prix' => $abonnement->prix,
                        'duree_validite' => $abonnement->duree_validite
                    ],
                    'redirect_url' => $result['data']['checkout_url'] ?? null
                ],
                'message' => 'Initialisation abonnement réussie'
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (Throwable $e) {

            Log::error('Init abonnement error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // SUCCESS URL
    // ===============================
    public function paymentSuccess(Request $request, NotificationService $notifService)
    {
        return $this->handleRedirect($request, $notifService, 'completed');
    }

    // ===============================
    // ERROR URL
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

            $response = Http::withHeaders([
                'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
                'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
            ])->get(env('GENIUS_URL') . "/payments/{$reference}");

            $result = $response->json();

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement introuvable'
                ], 400);
            }

            $metadata = $result['data']['metadata'] ?? null;

            if (!$metadata) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metadata introuvable'
                ], 400);
            }

            $paiementAbonnement = PaiementAbonnement::find($metadata['paiement_id']);

            if (!$paiementAbonnement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement introuvable'
                ], 404);
            }

            // anti double traitement
            if (!in_array($paiementAbonnement->statut, ['completed', 'failed'])) {

                $paiementAbonnement->update([
                    'statut' => $status,
                    'data' => $result
                ]);
            }

            $abonnement = Abonnement::find($metadata['abonnement_id']);

            $user = User::find($metadata['user_id']);

            if ($user && $user->device_token) {
                $notifService->sendToUser(
                    $user,
                    $status === 'completed'
                        ? "Abonnement activé ✅"
                        : "Paiement échoué ❌",
                    $status === 'completed'
                        ? "Votre abonnement {$abonnement->libelle} est actif"
                        : "Votre paiement a échoué"
                );
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paiementAbonnement->id,
                    'prix' => $paiementAbonnement->prix,
                    'statut' => $paiementAbonnement->statut,
                    'abonnement' => $abonnement,
                    'transaction' => $result
                ],
                'message' => $status === 'completed'
                    ? 'Paiement réussi'
                    : 'Paiement échoué'
            ], 200);

        } catch (Throwable $e) {

            Log::error('Abonnement redirect error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }

    // ===============================
    // WEBHOOK ABONNEMENT
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

            $paiement = PaiementAbonnement::find($metadata['paiement_id']);

            if (!$paiement) return response()->json(['error' => 'Not found'], 404);

            if (in_array($paiement->statut, ['completed', 'failed'])) {
                return response()->json(['success' => true]);
            }

            $status = match ($event) {
                'payment.success' => 'completed',
                'payment.failed', 'payment.cancelled', 'payment.expired' => 'failed',
                default => null
            };

            if (!$status) return response()->json(['success' => true]);

            $paiement->update([
                'statut' => $status,
                'data' => $payload
            ]);

            $user = User::find($metadata['user_id']);

            if ($user && $user->device_token) {
                $notifService->sendToUser(
                    $user,
                    $status === 'completed'
                        ? "Abonnement activé ✅"
                        : "Abonnement échoué ❌",
                    "Mise à jour de votre abonnement"
                );
            }

            return response()->json(['success' => true]);

        } catch (Throwable $e) {

            Log::error('Webhook abonnement error', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Server error'], 500);
        }
    }
}