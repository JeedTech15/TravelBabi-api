<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Paiement;
use App\Models\PaiementAbonnement;
use App\Models\PaiementPack;
use App\Models\Souscription;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CheckPaiementStatutController extends Controller
{
    public function check_status(Request $request, NotificationService $notifService, $reference)
    {
        try {

            $response = Http::withHeaders([
                'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
                'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
            ])->get(env('GENIUS_URL') . "/payments/{$reference}");

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
            | PACK
            |--------------------------------------------------------------------------
            */
            if (isset($metadata['pack_id'])) {

                $paiementPack = PaiementPack::find($metadata['paiement_pack_id'] ?? null);

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
                            'completed' => 'Abonnement activé ✅',
                            'failed' => 'Paiement échoué ❌',
                            default => 'Paiement en attente ⏳'
                        },
                        'subtitle' => match ($internalStatus) {
                            'completed' => 'Souscription mise à jour avec succès',
                            'failed' => 'Le paiement a échoué',
                            default => 'Vérification en cours'
                        }
                    ],
                    "message" => "Statut récupéré"
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | ABONNEMENT
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

                $user = User::find($metadata['user_id'] ?? null);

                /*
                |--------------------------------------------------------------------------
                | Souscription auto si paiement OK
                |--------------------------------------------------------------------------
                */
                if ($internalStatus === 'completed' && $user) {

                    $abonnement = Abonnement::find($metadata['abonnement_id']);

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
                if ($user && $user->device_token && in_array($internalStatus, ['completed', 'failed'])) {

                    $title = match ($internalStatus) {
                        'completed' => 'Abonnement activé ✅',
                        'failed' => 'Abonnement échoué ❌'
                    };

                    $body = match ($internalStatus) {
                        'completed' => 'Votre abonnement est maintenant actif',
                        'failed' => 'Votre paiement a échoué'
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
                            'completed' => 'Souscription mise à jour avec succès',
                            'failed' => 'Le paiement a échoué',
                            default => 'Vérification en cours'
                        }
                    ]
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

    private function calculateExpiration(Carbon $startDate, string $duration): Carbon
    {
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
}