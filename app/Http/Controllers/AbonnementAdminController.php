<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AbonnementAdminController extends Controller
{
    public function create_admin_admin(Request $request){
        try{
            $validated = Validator::make($request->all(), [
                'libelle' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'prix' => 'required|min:1',
                'duree_validite' => 'required|min:1',
                'populaire' => 'required|boolean'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $abonnement = Abonnement::create([
                'libelle' => $request->libelle,
                'description' =>$request->description,
                'prix' => $request->prix,
                'duree_validite' => $request->duree_validite,
                'populaire' => $request->populaire
            ]);

            return response()->json([
                'success' => true,
                'message' => "Abonnement créer avec succès!",
                'data' => [
                    'id' => $abonnement->id,
                    'libelle' => $abonnement->libelle,
                    'description' => $abonnement->description,
                    'prix' => $abonnement->prix."FCFA",
                    'duree_validite' => $abonnement->duree_validite." jours",
                    'populaire' => $abonnement->populaire
                ]
            ], 201);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la creation de l'abonnement: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la creation de l'abonnement: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function liste_admin_abonnement(){
        try{
            $abonnement = Abonnement::all()->map(function ($abonnement) {
                return [
                    'id' => $abonnement->id,
                    'libelle' => $abonnement->libelle,
                    'prix' => $abonnement->prix."FCFA",
                    'duree_validite' => $abonnement->duree_validite." jours",
                    'populaire' => $abonnement->populaire
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Liste des abonnements!",
                'data' => $abonnement
            ], 200);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de reccuperation de la liste des abonnements: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la reccuperation de la liste des abonnements: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function upadta_admin_abonnement(Request $request,$id_abonnement){
        try{
            $validated = Validator::make($request->all(), [
                'libelle' => 'required|string|max:255',
                'description' => 'required|string|max:255',
                'prix' => 'required|min:1',
                'duree_validite' => 'required|min:1',
                'populaire' => 'required|boolean'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $abonnement = Abonnement::where('id', $id_abonnement)->first();

            if(!$abonnement){
                return response()->json([
                    'success' => false,
                    'message' => "Abonnement introuvable!"
                ]);
            }

            if($abonnement){
                $abonnement->update([
                    'libelle' => $request->libelle,
                    'description' =>$request->description,
                    'prix' => $request->prix,
                    'duree_validite' => $request->duree_validite,
                    'populaire' => $request->populaire
                ]);

                return response()->json([
                'success' => true,
                'message' => "Abonnement mit a jour avec succès!",
                'data' => [
                    'id' => $abonnement->id,
                    'libelle' => $abonnement->libelle,
                    'description' => $abonnement->description,
                    'prix' => $abonnement->prix."FCFA",
                    'duree_validite' => $abonnement->duree_validite." jours",
                    'populaire' => $abonnement->populaire
                ]
            ], 201);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la maj lise a jour de l'abonnement: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la maj de l'abonnement: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_admin_abonnement(Request $request,$id_abonnement){
        try{
            $abonnement = Abonnement::where('id', $id_abonnement)->first();

            if(!$abonnement){
                return response()->json([
                    'success' => false,
                    'message' => "Abonnement introuvable!"
                ]);
            }

            if($abonnement){
                $abonnement->delete();
                return response()->json([
                    'success' => false,
                    'message' => "Abonnement supprimer"
                ], 204);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la suppression de l'abonnement: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la suppression de l'abonnement: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
