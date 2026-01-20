<?php

namespace App\Http\Controllers;

use App\Models\Pub;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PubAdminController extends Controller
{
    public function create_admin_pub(Request $request){
        try{
            $validated = Validator::make($request->all(), [
                'video_url' => 'required|file|mimes:mp3,wav,m4a',
                'duree_video' => 'required',
                'nbr_etoile' => 'required'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation"
                ], 422);
            }

            $path = $request->file('video_url')->store('pub', 'public');

            $pub = Pub::create([
                'video_url' => $path,
                'duree_video' => $request->duree_video,
                'nbr_etoile' => $request->nbr_etoile 
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pub créer avec succès",
                'data' => [
                    'id' => $pub->id,
                    'video_url' => $pub->video_url,
                    'duree_video' => $pub->duree_video,
                    'nbr_etoile' => $pub->videp_url
                ]
            ], 201);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la creation de la pub: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la creattion de la pub: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
