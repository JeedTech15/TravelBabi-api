<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use App\Models\PaiementAbonnement;
use App\Models\PaiementPack;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckPaiementStatutController extends Controller
{
    public function check_status(Request $request, NotificationService $notifService, $reference){
        try {

            $response = Http::withHeaders([
                'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
                'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
            ])->get(env('GENIUS_URL') . "/payments/{$reference}");

            // erreur côté Genius
            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de récupérer le paiement'
                ], 400);
            }

            $result = $response->json();
            $paymentData = $result['data'] ?? null;

            if (!$paymentData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de paiement introuvables'
                ], 400);
            }

            $metadata = $paymentData['metadata'] ?? null;

            if (!$metadata) {
                return response()->json([
                    'success' => false,
                    'message' => 'Metadata introuvable'
                ], 400);
            }

            $paymentStatus = $paymentData['status'] ?? 'pending';

            $internalStatus = match ($paymentStatus) {
                'completed' => 'completed',
                'failed', 'expired' => 'failed',
                default => 'pending'
            };

            /*
            |--------------------------------------------------------------------------
            | CAS 1 : PACK
            |--------------------------------------------------------------------------
            */
            if (isset($metadata['pack_id'])) {

                $paiementPack = PaiementPack::find($metadata['paiement_id'] ?? null);

                if (!$paiementPack) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Paiement pack introuvable'
                    ], 404);
                }

                if ($paiementPack->statut !== $internalStatus) {
                    $paiementPack->update([
                        'statut' => $internalStatus
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'type' => 'pack',
                        'status' => $internalStatus,
                        'title' => match ($internalStatus) {
                            'completed' => 'Pack activé ✅',
                            'failed' => 'Paiement échoué ❌',
                            default => 'Paiement en attente ⏳'
                        },
                        'subtitle' => match ($internalStatus) {
                            'completed' => 'Votre pack a été activé avec succès',
                            'failed' => 'Le paiement du pack a échoué',
                            default => 'Vérification du paiement en cours'
                        }
                    ],
                    'message' => 'Statut récupéré'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CAS 2 : ABONNEMENT
            |--------------------------------------------------------------------------
            */
            if (isset($metadata['abonnement_id'])) {

                $paiement = Paiement::find($metadata['paiement_id'] ?? null);
                $paiementAbonnement = PaiementAbonnement::find(
                    $metadata['paiement_abonnement_id'] ?? null
                );

                if (!$paiement) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Paiement abonnement introuvable'
                    ], 404);
                }

                if ($paiement->status_paiement !== $internalStatus) {
                    $paiement->update([
                        'status_paiement' => $internalStatus
                    ]);
                }

                if ($paiementAbonnement && $paiementAbonnement->statut !== $internalStatus) {
                    $paiementAbonnement->update([
                        'statut' => $internalStatus,
                        'data' => $result
                    ]);
                }

                // notification
                $isFinal = in_array($internalStatus, ['completed', 'failed']);
                $user = User::find($metadata['user_id'] ?? null);

                if ($user && $user->device_token && $isFinal) {

                    $title = match ($internalStatus) {
                        'completed' => "Abonnement activé ✅",
                        'failed' => "Abonnement échoué ❌",
                    };

                    $body = match ($internalStatus) {
                        'completed' => "Votre abonnement est actif",
                        'failed' => "Votre paiement a échoué",
                    };

                    $notifService->sendToUser($user, $title, $body);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'type' => 'abonnement',
                        'status' => $internalStatus,
                        'title' => match ($internalStatus) {
                            'completed' => 'Abonnement activé ✅',
                            'failed' => 'Paiement échoué ❌',
                            default => 'Paiement en attente ⏳'
                        },
                        'subtitle' => match ($internalStatus) {
                            'completed' => 'Votre abonnement est maintenant actif',
                            'failed' => 'Le paiement de votre abonnement a échoué',
                            default => 'Vérification du paiement en cours'
                        }
                    ],
                    'message' => 'Statut récupéré'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Type de paiement inconnu'
            ], 400);

        } catch (Throwable $e) {

            Log::error('Check status error', [
                'error' => $e->getMessage(),
                'reference' => $reference
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }
}
