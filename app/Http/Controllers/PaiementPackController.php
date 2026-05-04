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

    public function initialiser_paiement(Request $request, NotificationService $notifService){
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

            // ✅ paiement pack
            $paiementPack = PaiementPack::create([
                'id_user' => $user->id,
                'id_pack' => $pack->id,
                'prix' => $pack->prix,
                'statut' => 'pending'
            ]);

            // ✅ paiement principal
            $paiement = Paiement::create([
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
                    "paiement_id" => $paiement->id, // ✅ FIX
                    "paiement_pack_id" => $paiementPack->id,
                    "user_id" => $user->id,
                    "pack_id" => $pack->id
                ]
            ];

            $response = Http::withHeaders([
                'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
                'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
            ])->post(env('GENIUS_URL') . '/payments', $payload);

            $result = $response->json();

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement rejeté'
                ], 422);
            }

            $paiementPack->update([
                'reference' => $result['data']['reference'] ?? null,
                'data' => $result
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paiementPack->id,
                    'prix' => $paiementPack->prix,
                    'statut' => $paiementPack->statut,
                    'pack' => [
                        'id' => $pack->id,
                        'nbr_etoile' => $pack->nbr_etoile,
                        'libelle' => $pack->libelle,
                        'prix' => $pack->prix
                    ],
                    'redirect_url' => $result['data']['checkout_url'] ?? null
                ],
                "message" => "Initialisation du paiement effectué"
            ], 200, [], JSON_UNESCAPED_SLASHES);

        } catch (Throwable $e) {
            Log::error('Init paiement error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }

    private function processPayment(array $paymentData){
        $metadata = $paymentData['metadata'] ?? null;

        if (!$metadata || !isset($metadata['paiement_pack_id'])) {
            return null;
        }

        $paiementPack = PaiementPack::find($metadata['paiement_pack_id']);
        if (!$paiementPack) return null;

        $status = $paymentData['status'] ?? 'pending';

        if (in_array($status, ['pending', 'processing'])) {
            return $paiementPack;
        }

        $internalStatus = match ($status) {
            'completed' => 'completed',
            'failed', 'expired' => 'failed',
            default => 'pending'
        };

        // ✅ éviter double traitement
        if ($paiementPack->statut === 'completed') {
            return $paiementPack;
        }

        $paiementPack->update([
            'statut' => $internalStatus,
            'data' => $paymentData
        ]);

        // ✅ update paiement principal
        if (isset($metadata['paiement_id'])) {
            $paiement = Paiement::find($metadata['paiement_id']);
            if ($paiement) {
                $paiement->update([
                    'status_paiement' => $internalStatus
                ]);
            }
        }

        // 🔥 CREDIT ÉTOILES (UNE SEULE FOIS)
        if ($internalStatus === 'completed') {

            $user = User::find($metadata['user_id']);
            $pack = Pack::find($metadata['pack_id']);

            if ($user && $pack) {
                $user->increment('nbr_etoile', $pack->nbr_etoile);
            }
        }

        return $paiementPack;
    }

    public function handleWebhook(Request $request){
        try {

            $payload = $request->all();
            $paymentData = $payload['data'] ?? null;

            if (!$paymentData) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $paiementPack = $this->processPayment($paymentData);

            if (!$paiementPack) {
                return response()->json(['error' => 'Not found'], 404);
            }

            return response()->json(['success' => true]);

        } catch (Throwable $e) {
            Log::error('Webhook error', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error'], 500);
        }
    }

    // public function check_status(Request $request, $reference){
    //     try {

    //         $response = Http::withHeaders([
    //             'X-API-Key' => env('GENIUS_API_KEY_PUBLIC'),
    //             'X-API-Secret' => env('GENIUS_API_KEY_SECRET'),
    //         ])->get(env('GENIUS_URL') . "/payments/{$reference}");

    //         if ($response->failed()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Erreur récupération paiement'
    //             ], 400);
    //         }

    //         $result = $response->json();
    //         $paymentData = $result['data'] ?? null;

    //         if (!$paymentData) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Données introuvables'
    //             ], 400);
    //         }

    //         $paiementPack = $this->processPayment($paymentData);

    //         if (!$paiementPack) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Paiement introuvable'
    //             ], 404);
    //         }

    //         $metadata = $paymentData['metadata'] ?? null;

    //         $pack = null;
    //         if ($metadata && isset($metadata['pack_id'])) {
    //             $pack = Pack::find($metadata['pack_id']);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => [
    //                 'id' => $paiementPack->id,
    //                 'prix' => $paiementPack->prix,
    //                 'statut' => $paiementPack->statut,

    //                 'pack' => $pack ? [
    //                     'id' => $pack->id,
    //                     'libelle' => $pack->libelle,
    //                     'nbr_etoile' => $pack->nbr_etoile,
    //                     'prix' => $pack->prix
    //                 ] : null,

    //                 'transaction' => [
    //                     'reference' => $paymentData['reference'] ?? null,
    //                     'status' => $paymentData['status'] ?? null,
    //                     'payment_method' => $paymentData['payment_method'] ?? null,
    //                     'amount' => $paymentData['amount'] ?? null,
    //                     'fees' => $paymentData['fees'] ?? null,
    //                     'net_amount' => $paymentData['net_amount'] ?? null,
    //                     'created_at' => $paymentData['created_at'] ?? null,
    //                     'completed_at' => $paymentData['completed_at'] ?? null
    //                 ]
    //             ],
    //             'message' => match ($paiementPack->statut) {
    //                 'completed' => 'Paiement réussi',
    //                 'failed' => 'Paiement échoué',
    //                 default => 'Paiement en attente'
    //             }

    //         ], 200, [], JSON_UNESCAPED_SLASHES);

    //     } catch (Throwable $e) {

    //         Log::error('Check status error', [
    //             'error' => $e->getMessage()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur serveur'
    //         ], 500);
    //     }
    // }
}