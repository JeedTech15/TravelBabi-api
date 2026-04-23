<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendToUser(User $user, string $title, string $message, ?string $body = null)
    {
        // 1. Sauvegarde en base
        $notification = Notification::create([
            'utilisateur_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'body' => $body,
        ]);

        // 2. Envoi push
        if ($user->device_token) {
            $this->sendPush($user->device_token, $title, $message);
        }

        return $notification;
    }

    private function sendPush($token, $title, $message)
    {
        $payload = [
            'to' => $token,
            'sound' => 'default',
            'title' => $title,
            'body' => $message,
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip, deflate',
            'Content-Type' => 'application/json',
        ])->post('https://exp.host/--/api/v2/push/send', $payload);

        // Debug si besoin
        if ($response->failed()) {
            Log::error('Expo push error', [
                'response' => $response->body()
            ]);
        }

        return $response->json();
    }
}