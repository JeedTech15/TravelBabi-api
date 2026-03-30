<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class AbonnementUserController extends Controller
{
    public function abonnements(Request $request){
        try{
            $abonnements = Abonnement::orderBy('created_at', 'desc')->get();
            if($abonnements->isEmpty()){
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Aucun abonnement trouvé pour l’instant'
                ],200);
            }

            $data = $abonnements->map(function($abonnement){
                return [
                    'id' => $abonnement->id,
                    'title' => $abonnement->libelle,
                    'description' => $abonnement->description,
                    'price' => $abonnement->prix,
                    'duration' => $abonnement->duree_validite,
                    'is_popular' => 0
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Liste des abonnements affichées avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage des abonnements',
                'erreur' => $e->getMessage()
            ],500);
        }
    }
}
