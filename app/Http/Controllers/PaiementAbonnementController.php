<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Paiement;
use App\Models\PaiementAbonnement;
use App\Models\Souscription;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class PaiementAbonnementController extends Controller
{
    // ===============================
    // INIT PAIEMENT ABONNEMENT
    // ===============================
    public function initialiser_paiement(Request $request, NotificationService $notifService){
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

            /*
            |--------------------------------------------------------------------------
            | Vérifier abonnement actif
            |--------------------------------------------------------------------------
            */
            $souscriptionActive = Souscription::where('utilisateur_id', $user->id)
                ->whereNotNull('expire_abonnement')
                ->where('expire_abonnement', '>', Carbon::now())
                ->first();

            if ($souscriptionActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous avez déjà un abonnement actif'
                ], 409);
            }

            $abonnement = Abonnement::find($request->id_abonnement);

            if (!$abonnement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Abonnement introuvable'
                ], 404);
            }

            $paiementAbonnement = PaiementAbonnement::create([
                'id_user' => $user->id,
                'id_abonnement' => $abonnement->id,
                'prix' => $abonnement->prix,
                'statut' => 'pending'
            ]);

            $paiement = Paiement::create([
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
                    "paiement_id" => $paiement->id,
                    "paiement_abonnement_id" => $paiementAbonnement->id,
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

            $paiementAbonnement->update([
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

            Log::error('Init abonnement error', [
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
    // WEBHOOK
    // ===============================
    public function handleWebhook(Request $request, NotificationService $notifService){
        try {

            $signature = $request->header('X-Webhook-Signature');
            $timestamp = $request->header('X-Webhook-Timestamp');

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

            if (!$metadata || !isset($metadata['paiement_id'])) {
                return response()->json(['error' => 'Invalid metadata'], 400);
            }

            $paiement = Paiement::find($metadata['paiement_id']);
            $paiementAbonnement = PaiementAbonnement::find(
                $metadata['paiement_abonnement_id'] ?? null
            );

            if (!$paiement) {
                return response()->json(['error' => 'Paiement introuvable'], 404);
            }

            if ($paiement->status_paiement !== 'pending') {
                return response()->json(['success' => true]);
            }

            $paymentStatus = $payload['data']['status'] ?? null;

            $newStatus = match ($paymentStatus) {
                'completed' => 'completed',
                'failed', 'expired' => 'failed',
                default => null
            };

            if (!$newStatus) {
                return response()->json(['success' => true]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update paiements
            |--------------------------------------------------------------------------
            */
            $paiement->update([
                'status_paiement' => $newStatus
            ]);

            if ($paiementAbonnement) {
                $paiementAbonnement->update([
                    'statut' => $newStatus,
                    'data' => $payload
                ]);
            }

            $user = User::find($metadata['user_id'] ?? null);

            /*
            |--------------------------------------------------------------------------
            | Création / update souscription si paiement validé
            |--------------------------------------------------------------------------
            */
            if ($newStatus === 'completed' && $user) {

                $abonnement = Abonnement::find($metadata['abonnement_id'] ?? null);

                if ($abonnement) {

                    $now = Carbon::now();
                    $expireAt = $this->calculateExpiration(
                        $now,
                        $abonnement->duree_validite
                    );

                    $souscription = Souscription::where(
                        'utilisateur_id',
                        $user->id
                    )->first();

                    if ($souscription) {

                        $souscription->update([
                            'abonnement_id' => $abonnement->id,
                            'creation_abonnement' => $now,
                            'expire_abonnement' => $expireAt
                        ]);

                    } else {

                        Souscription::create([
                            'id' => Str::uuid(),
                            'utilisateur_id' => $user->id,
                            'abonnement_id' => $abonnement->id,
                            'creation_abonnement' => $now,
                            'expire_abonnement' => $expireAt
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Notification
            |--------------------------------------------------------------------------
            */
            if ($user && $user->device_token) {

                $notifService->sendToUser(
                    $user,
                    $newStatus === 'completed'
                        ? 'Abonnement activé ✅'
                        : 'Abonnement échoué ❌',
                    $newStatus === 'completed'
                        ? 'Votre abonnement est maintenant actif'
                        : 'Votre paiement a échoué'
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

    private function calculateExpiration(Carbon $startDate, string $duration): Carbon{
        preg_match('/(\d+)\s*(Jour|Jour\(s\)|Semaine|Semaine\(s\)|Mois|An|An\(s\))/i', $duration, $matches);

        $value = (int) ($matches[1] ?? 0);
        $unit = strtolower($matches[2] ?? '');

        return match (true) {
            str_contains($unit, 'jour') => $startDate->copy()->addDays($value),
            str_contains($unit, 'semaine') => $startDate->copy()->addWeeks($value),
            str_contains($unit, 'mois') => $startDate->copy()->addMonths($value),
            str_contains($unit, 'an') => $startDate->copy()->addYears($value),
            default => $startDate->copy()
        };
    }

    // public function check_status(Request $request, NotificationService $notifService, $reference){
    //     try {

    //         $reference = $reference;

    //         // 🔥 appel API Genius
    //         $response = Http::withHeaders([
    //             'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
    //             'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
    //         ])->get(env('GENIUS_URL') . "/payments/{$reference}");

    //         if ($response->failed()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Impossible de récupérer le paiement',
    //                 'erreur' => $response->json('error.message') ?? 'Erreur inconnue'
    //             ], 400);
    //         }

    //         $result = $response->json();
    //         $paymentData = $result['data'] ?? null;

    //         if (!$paymentData) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Données de paiement introuvables'
    //             ], 400);
    //         }

    //         // ✅ metadata fiable depuis Genius
    //         $metadata = $paymentData['metadata'] ?? null;
    //         $paymentStatus = $paymentData['status'] ?? null;

    //         if (!$metadata || !isset($metadata['paiement_id'])) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Metadata introuvable'
    //             ], 400);
    //         }

    //         // ✅ récupération propre
    //         $paiement = Paiement::find($metadata['paiement_id']);
    //         $paiementAbonnement = PaiementAbonnement::find($metadata['paiement_abonnement_id'] ?? null);

    //         if (!$paiement) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Paiement introuvable'
    //             ], 404);
    //         }

    //         // 🔁 mapping statut Genius → interne
    //         $internalStatus = match ($paymentStatus) {
    //             'completed' => 'completed',
    //             'failed', 'expired' => 'failed',
    //             default => 'pending'
    //         };

    //         // ✅ update paiement principal
    //         if ($paiement->status_paiement !== $internalStatus) {
    //             $paiement->update([
    //                 'status_paiement' => $internalStatus
    //             ]);
    //         }

    //         // ✅ update abonnement
    //         if ($paiementAbonnement && $paiementAbonnement->statut !== $internalStatus) {
    //             $paiementAbonnement->update([
    //                 'statut' => $internalStatus,
    //                 'data' => $result
    //             ]);
    //         }

    //         // 🔔 notif uniquement si statut final
    //         $isFinal = in_array($internalStatus, ['completed', 'failed']);
    //         $user = User::find($metadata['user_id']);

    //         if ($user && $user->device_token && $isFinal) {

    //             $title = match ($internalStatus) {
    //                 'completed' => "Abonnement activé ✅",
    //                 'failed' => "Abonnement échoué ❌",
    //                 default => "Statut mis à jour"
    //             };

    //             $body = match ($internalStatus) {
    //                 'completed' => "Votre abonnement est actif",
    //                 'failed' => "Votre paiement a échoué",
    //                 default => "Paiement en cours"
    //             };

    //             $notifService->sendToUser($user, $title, $body);
    //         }

    //         // 📦 récupération abonnement
    //         $abonnement = null;
    //         if (isset($metadata['abonnement_id'])) {
    //             $abonnement = Abonnement::find($metadata['abonnement_id']);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'id' => $paiementAbonnement?->id,
    //                 'prix' => $paiementAbonnement?->prix,
    //                 'statut' => $internalStatus,
    //                 'abonnement' => $abonnement ? [
    //                     'id' => $abonnement->id,
    //                     'libelle' => $abonnement->libelle,
    //                     'prix' => $abonnement->prix,
    //                     'duree_validite' => $abonnement->duree_validite
    //                 ] : null,
    //                 'transaction' => [
    //                     'reference' => $paymentData['reference'] ?? null,
    //                     'status' => $paymentStatus,
    //                     'payment_method' => $paymentData['payment_method'] ?? null,
    //                     'amount' => $paymentData['amount'] ?? null,
    //                     'fees' => $paymentData['fees'] ?? null,
    //                     'net_amount' => $paymentData['net_amount'] ?? null,
    //                     'created_at' => $paymentData['created_at'] ?? null,
    //                     'completed_at' => $paymentData['completed_at'] ?? null
    //                 ]
    //             ],
    //             'message' => match ($internalStatus) {
    //                 'completed' => 'Paiement réussi',
    //                 'failed' => 'Paiement échoué',
    //                 default => 'Paiement en attente'
    //             }
    //         ], 200, [], JSON_UNESCAPED_SLASHES);

    //     } catch (Throwable $e) {

    //         Log::error('Check abonnement error', [
    //             'error' => $e->getMessage(),
    //             'reference' => $request->query('reference')
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur serveur',
    //             'erreur' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}