<?php

namespace App\Http\Controllers;

use App\Models\Faqs;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FaqsController extends Controller
{
    public function store_faqs(Request $request){
        $validated = Validator::make($request->all(), [
            'question' => 'required|string',
            'reponse' => 'required|string'
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }

        try{
            $faqs = Faqs::create([
                'question' => $request->question,
                'reponse' => $request->reponse
            ]);

            return response()->json([
                'success' => true,
                'message' => "Faqs créer avec succès"
            ], 201);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la création de la faqs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la création de la faqs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function index_faqs(){
        try{
            $faqs = Faqs::all();

            return response()->json([
                'success' => true,
                'message' => "Liste des faqs!",
                'data' => $faqs
            ], 200);

        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la récupération des faqs : ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function update_faqs(Request $request, $id){
        $validated = Validator::make($request->all(), [
            'question' => 'required|string',
            'reponse' => 'required|string'
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }

        try{
            $faqs = Faqs::find($id);

            if(!$faqs){
                return response()->json([
                    'success' => false,
                    'message' => "Faq introuvable"
                ], 404);
            }

            $faqs->update([
                'question' => $request->question,
                'reponse' => $request->reponse
            ]);

            return response()->json([
                'success' => true,
                'message' => "Faq modifiée avec succès",
                'data' => $faqs
            ], 200);

        }catch(QueryException $e){
            Log::error("Erreur sql lors de la modification de la faq : ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la modification de la faq : ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_faqs($id){
        try{
            $faqs = Faqs::find($id);

            if(!$faqs){
                return response()->json([
                    'success' => false,
                    'message' => "Faq introuvable"
                ], 404);
            }

            $faqs->delete();

            return response()->json([
                'success' => true,
                'message' => "Faq supprimée avec succès"
            ], 204);

        }catch(QueryException $e){
            Log::error("Erreur sql lors de la suppression de la faq : ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la suppression de la faq : ".$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
