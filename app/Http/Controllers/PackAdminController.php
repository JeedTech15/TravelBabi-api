<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PackAdminController extends Controller
{
    public function create_pack(Request $request){
        try{
            $validated = Validator::make($request->all(), [
                'nbr_etoile' => 'required|integer|min:1',
                'libelle' => 'required|string|max:255',
                'prix' => 'required|integer|min:1',
                'populaire' => 'required|boolean'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation!",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $packs = Pack::create([
                'nbr_etoile' => $request->nbr_etoile,
                'libelle' => $request->libelle,
                'prix' => $request->prix,
                'populaire' => $request->populaire
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pack creer avec succès",
                'data' => [
                    'id' => $packs->id,
                    'nbr_etoile' => $packs->nbr_etoile,
                    'libelle' => $packs->libelle,
                    'prix' => $packs->prix."FCFA",
                    'populaire' => $packs->populaire
                ]
            ], 201);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la creation du pack: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la creation du pack: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function liste_pack_admin(){
        try{
            $packs = Pack::all()->map(function ($packs) {
                return [
                    'id' => $packs->id,
                    'nbr_etoile' => $packs->nbr_etoile,
                    'libelle' => $packs->libelle,
                    'prix' => $packs->prix."FCFA",
                    'populaire' => $packs->populaire
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Liste des packs",
                'data' => $packs
            ], 200);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la reccuperation de la liste des packs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreir serveur lors de la reccuperation de la liste des packs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_pack_admin(Request $request,$id_pack){
        try{

            $validated = Validator::make($request->all(), [
                'nbr_etoile' => 'required|integer|min:1',
                'libelle' => 'required|string|max:255',
                'prix' => 'required|integer|min:1',
                'populaire' => 'required|boolean'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation!",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $packs = Pack::where('id', $id_pack)->first();

            if(!$packs){
                return response()->json([
                    'success' => false,
                    'message' => "Pack introuvable!"
                ], 404);
            }

            $packs->update([
                'nbr_etoile' => $request->nbr_etoile,
                'libelle' => $request->libelle,
                'prix' => $request->prix,
                'populaire' => $request->populaire
            ]);

            return response()->json([
                'success' => true,
                'message' => "Pack mit a jour avec succès!",
                'data' => [
                    'id' => $packs->id,
                    'nbr_etoile' => $packs->nbr_etoile,
                    'libelle' => $packs->libelle,
                    'prix' => $packs->prix."FCFA",
                    'populaire' => $packs->populaire
                ]
            ]);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la mise a jour du pack: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'mesage' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la mise a jour du pack: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_pack_admin(Request $request,$id_pack){
        try{
            $packs = Pack::where('id', $id_pack)->first();

            if($packs){
                $packs->delete();
                return response()->json([
                    'success' => true,
                    'message' => "Pack supprimer avec succès"
                ], 204);
            }elseif(!$packs){
                return response()->json([
                    'success' => false,
                    'message' => "Pack inctrouvable!"
                ], 404);
            }
        }catch(QueryException $e){
            Log::error("Erreur lors de la suppression du pack: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
