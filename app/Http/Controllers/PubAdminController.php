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
                'video_url' => 'required|file|mimes:mp4,wav,m4a',
                'duree_video' => 'required',
                'nbr_etoile' => 'required'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation",
                    'erreur' => $validated->errors()
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
                    'nbr_etoile' => $pub->nbr_etoile
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

    public function liste_admin_pubs(){
        try{
            $pubs = Pub::all()->map(function ($pubs) {
                return [
                    'id' => $pubs->id,
                    'video_url' => $pubs->video_url,
                    'duree_video' => $pubs->duree_video,
                    'nbr_etoile' => $pubs->nbr_etoile
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Liste des pubs!",
                'data' => $pubs
            ], 200);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la reccuperation de la liste des pubs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la reccuperation de la liste des pubs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_admin_pub(Request $request,$id_pub){
        try{
                $validated = Validator::make($request->all(), [
                'video_url' => 'required|file|mimes:mp4,wav,m4a',
                'duree_video' => 'required',
                'nbr_etoile' => 'required'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $path = $request->file('video_url')->store('pub', 'public');

            $pubs = Pub::where('id', $id_pub)->first();

            if(!$pubs){
                return response()->json([
                    'success' => false,
                    'message' => "Pub introuvable!"
                ], 404);
            }

            if($pubs){
                $pubs->update([
                    'video_url' => $path,
                    'duree_video' => $request->duree_video,
                    'nbr_etoile' => $request->nbr_etoile 
                ]);

                return response()->json([
                'success' => true,
                'message' => "Pub mit a jour avec succès",
                'data' => [
                    'id' => $pubs->id,
                    'video_url' => $pubs->video_url,
                    'duree_video' => $pubs->duree_video,
                    'nbr_etoile' => $pubs->nbr_etoile
                ]
            ], 201);
        }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la maj de la pub: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(QueryException $e){
            Log::error("Erreur serveur lors de la maj de la pub: ". $e->getMessage());
            return response()->json([
                'success' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_admin_pub(Request $request,$id_pub){
        try{
            $pubs = Pub::where('id', $id_pub)->first();

            if(!$pubs){
                return response()->json([
                    'success' => false,
                    'message' => "Pub introuvable!"
                ], 404);
            }

            if($pubs){
                $pubs->delete();
                return response()->json([
                    'success' => true,
                    'message' => "Pub supprimer!"
                ], 204);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la suppression de la pub: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la suppression de la pub: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
