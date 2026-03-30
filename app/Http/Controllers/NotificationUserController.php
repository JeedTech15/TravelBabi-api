<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Throwable;

class NotificationUserController extends Controller
{
    public function notifications(Request $request){
        try{
            $user = User::find($request->user()->id);
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable'
                ],404);
            }

            $nofications = Notification::where('utilisateur_id', $user->id)->orderBy('created_at', 'desc')->get();

            if($nofications->isEmpty()){
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Aucune notifications pour le moment'
                ],200);
            }
            $data = $nofications->map(function($nofication){
                return [
                    'id' => $nofication->id,
                    'title' => $nofication->title,
                    'message' => $nofication->message
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Notifications affichées avec succès'
            ],200);
            
        }
        catch(Throwable $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage des notifications',
                'erreur' => $e->getMessage()
            ],500);
        }
    }
}
