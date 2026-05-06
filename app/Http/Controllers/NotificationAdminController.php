<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationAdminController extends Controller
{
    public function notification_all(Request $request){
        $request->validate([
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        try {

            // 🔍 récupérer users avec device_token
            $users = User::whereNotNull('device_token')->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur trouvé'
                ], 404);
            }

            // 📱 récupérer tokens
            $tokens = $users->pluck('device_token')->toArray();

            // 🔥 chunk (important pour Expo)
            $chunks = array_chunk($tokens, 100);

            foreach ($chunks as $chunk) {
                Http::post('https://api.expo.dev/v2/push/send', [
                    'to' => $chunk,
                    'sound' => 'default',
                    'title' => $request->title,
                    'body' => $request->message,
                ]);
            }

            // 💾 sauvegarde BDD
            foreach ($users as $user) {
                Notification::create([
                    'utilisateur_id' => $user->id,
                    'title' => $request->title,
                    'message' => $request->message,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification envoyée à tous les utilisateurs'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function notification_multiple(Request $request){
        $request->validate([
            'device_tokens' => 'required|array',
            'device_tokens.*' => 'required|string',
            'title' => 'required|string',
            'message' => 'required|string',
        ]);

        try {

            // 🔍 récupérer users concernés
            $users = User::whereIn('device_token', $request->device_tokens)->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun utilisateur trouvé avec ces tokens'
                ], 404);
            }

            // 📱 tokens
            $tokens = $users->pluck('device_token')->toArray();

            // 🔥 chunk
            $chunks = array_chunk($tokens, 100);

            foreach ($chunks as $chunk) {
                Http::post('https://api.expo.dev/v2/push/send', [
                    'to' => $chunk,
                    'sound' => 'default',
                    'title' => $request->title,
                    'body' => $request->message,
                ]);
            }

            // 💾 sauvegarde
            foreach ($users as $user) {
                Notification::create([
                    'utilisateur_id' => $user->id,
                    'title' => $request->title,
                    'message' => $request->message,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification envoyée aux utilisateurs sélectionnés'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

