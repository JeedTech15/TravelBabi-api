<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackUserController extends Controller
{
    public function packs(Request $request){
        try{
            $packs = Pack::orderBy('created_at')->get();
            if($packs->isEmpty()){
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Aucun pack pour le moment'
                ],200);
            }

            $data = $packs->map(function($pack){
                return [
                    'id' => $pack->id,
                    'num_star' => $pack->nbr_etoile,
                    'price' => $pack->prix,
                    'is_popular' => 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Liste des packs affichés avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage de la liste des packs',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    public function buy_pack(Request $request){
        $validator = Validator::make($request->all(), [
            'id_pack' => 'required'
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $pack = Pack::find($request->id_pack);
            if(!$pack){
                return response()->json([
                    'success' => false,
                    'message' => 'Pack non trouvé'
                ],404);
            }

            $auth = $request->user();
            $user = User::find($auth->id); 
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable'
                ],404);
            }

            $user->nbr_etoile += $pack->nbr_etoile;
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'email' => $user->email,
                    'numero' => $user->numero,
                    'image' => $user->image,
                    'nbr_etoile' => $user->nbr_etoile,
                ],
                'message' => 'Pack Acheté avec succès'
            ]);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’achat de pack',
                'erreur' => $e->getMessage()
            ],500);
        }
    }
}
