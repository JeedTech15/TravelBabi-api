<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use App\Models\Pack;
use App\Models\Paiement;
use App\Models\PaiementAbonnement;
use App\Models\PaiementPack;
use App\Models\Souscription;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request, NotificationService $notifService){
        try {

            /*
            |--------------------------------------------------------------------------
            | Vérification signature webhook
            |--------------------------------------------------------------------------
            */
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

            $paymentData = $payload['data'] ?? null;

            if (!$paymentData) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }

            $metadata = $paymentData['metadata'] ?? null;

            if (!$metadata) {
                return response()->json(['error' => 'Invalid metadata'], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | Détection type paiement
            |--------------------------------------------------------------------------
            */
            if (isset($metadata['paiement_pack_id'])) {
                return $this->handlePackPayment($paymentData);
            }

            if (isset($metadata['paiement_abonnement_id'])) {
                return $this->handleAbonnementPayment(
                    $paymentData,
                    $notifService
                );
            }

            return response()->json(['error' => 'Unknown payment type'], 400);

        } catch (Throwable $e) {

            Log::error('Webhook error', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Server error'], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TRAITEMENT ABONNEMENT
    |--------------------------------------------------------------------------
    */
    private function handleAbonnementPayment(array $paymentData, NotificationService $notifService){
        $metadata = $paymentData['metadata'];

        $paiement = Paiement::find($metadata['paiement_id'] ?? null);
        $paiementAbonnement = PaiementAbonnement::find(
            $metadata['paiement_abonnement_id']
        );

        if (!$paiement) {
            return response()->json(['error' => 'Paiement introuvable'], 404);
        }

        if ($paiement->status_paiement !== 'pending') {
            return response()->json(['success' => true]);
        }

        $paymentStatus = $paymentData['status'] ?? null;

        $newStatus = match ($paymentStatus) {
            'completed' => 'completed',
            'failed', 'expired' => 'failed',
            default => null
        };

        if (!$newStatus) {
            return response()->json(['success' => true]);
        }

        $paiement->update([
            'status_paiement' => $newStatus
        ]);

        if ($paiementAbonnement) {
            $paiementAbonnement->update([
                'statut' => $newStatus,
                'data' => $paymentData
            ]);
        }

        $user = User::find($metadata['user_id'] ?? null);

        if ($newStatus === 'completed' && $user) {

            $abonnement = Abonnement::find(
                $metadata['abonnement_id'] ?? null
            );

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
    }


    /*
    |--------------------------------------------------------------------------
    | TRAITEMENT PACK
    |--------------------------------------------------------------------------
    */
    private function handlePackPayment(array $paymentData){
        $metadata = $paymentData['metadata'];

        $paiementPack = PaiementPack::find(
            $metadata['paiement_pack_id']
        );

        if (!$paiementPack) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $status = $paymentData['status'] ?? 'pending';

        if (in_array($status, ['pending', 'processing'])) {
            return response()->json(['success' => true]);
        }

        $internalStatus = match ($status) {
            'completed' => 'completed',
            'failed', 'expired' => 'failed',
            default => 'pending'
        };

        if ($paiementPack->statut === 'completed') {
            return response()->json(['success' => true]);
        }

        $paiementPack->update([
            'statut' => $internalStatus,
            'data' => $paymentData
        ]);

        if (isset($metadata['paiement_id'])) {
            $paiement = Paiement::find($metadata['paiement_id']);

            if ($paiement) {
                $paiement->update([
                    'status_paiement' => $internalStatus
                ]);
            }
        }

        if ($internalStatus === 'completed') {

            $user = User::find($metadata['user_id']);
            $pack = Pack::find($metadata['pack_id']);

            if ($user && $pack) {
                $user->increment(
                    'nbr_etoile',
                    $pack->nbr_etoile
                );
            }
        }

        return response()->json(['success' => true]);
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
}
