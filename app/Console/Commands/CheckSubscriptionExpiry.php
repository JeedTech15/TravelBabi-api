<?php

namespace App\Console\Commands;

use App\Models\Souscription;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscriptions:check-expiry';

    protected $description = 'Vérifie les abonnements et envoie les rappels';

    public function handle(NotificationService $notifService)
    {
        $today = Carbon::now();

        $souscriptions = Souscription::with(['utilisateur', 'abonnement'])
            ->whereNotNull('expire_abonnement')
            ->get();

        foreach ($souscriptions as $souscription) {

            $expireAt = Carbon::parse($souscription->expire_abonnement);
            $daysLeft = $today->diffInDays($expireAt, false);

            /*
            |--------------------------------------------------------------------------
            | Expiré
            |--------------------------------------------------------------------------
            */
            if ($daysLeft < 0) {

                $souscription->update([
                    'creation_abonnement' => null,
                    'expire_abonnement' => null
                ]);

                if ($souscription->utilisateur?->device_token) {
                    $notifService->sendToUser(
                        $souscription->utilisateur,
                        'Abonnement expiré ❌',
                        'Votre abonnement a expiré'
                    );
                }

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Rappels
            |--------------------------------------------------------------------------
            */
            if (in_array($daysLeft, [7, 5, 3, 2, 1])) {

                if ($souscription->utilisateur?->device_token) {

                    $notifService->sendToUser(
                        $souscription->utilisateur,
                        'Expiration abonnement ⏳',
                        "Votre abonnement expire dans {$daysLeft} jour(s)"
                    );
                }
            }
        }

        $this->info('Vérification terminée');
    }
}