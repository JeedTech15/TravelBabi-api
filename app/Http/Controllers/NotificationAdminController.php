<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationAdminController extends Controller
{
    public function notification_client(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        
        $user = User::where('device_token', $request->device_token)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun client trouvé avec ce device token'
            ], 404);
        }

        try {
            
            $response = Http::post('https://api.expo.dev/v2/push/send', [
                'to' => $request->device_token,
                'sound' => 'default',
                'title' => $request->title,
                'body' => $request->message,
            ]);

            
            if ($response->failed()) {
                Log::error('Expo HTTP Error', [
                    'response' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de la notification'
                ], 500);
            }

            $res = $response->json();

            
            if (
                isset($res['data'][0]['status']) &&
                $res['data'][0]['status'] !== 'ok'
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur Expo',
                    'error' => $res
                ], 500);
            }

            
            $notification = Notification::create([
                'user_id' => $user->id,
                'title' => $request->title,
                'message' => $request->message,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification envoyée avec succès',
                'data' => $notification
            ], 201);

        } catch (\Exception $e) {
            Log::error('Notification Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ], 500);
        }
    }
}

