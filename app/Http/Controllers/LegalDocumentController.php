<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LegalDocumentController extends Controller
{
    public function strore_confidentilité(Request $request){
        $validated = Validator::make($request->all(), [
            'type' => 'required|string',
            'title' => 'required|string',
            'content' => 'required|string',
            'version' => 'required|string'
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }

        try{
                $conf = LegalDocument::create([
                'type' => $request->type,
                'title' => $request->title,
                'content' => $request->content,
                'version' => $request->version
            ]);

            return response()->json([
                'success' => true,
                'message' => "Confidentilé créer avec succès",
                'data' => $conf
            ], 201);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la création de la confidentialité: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la création de confidentilité: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_confidentialite(Request $request,$id){
        $validated = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
            'version' => 'required|string'
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }

        try{
            $conf = LegalDocument::where('id', $id)->first();

            if(!$conf){
                return response()->json([
                    'success' => false,
                    'message' => "Confidentilité introuvable"
                ], 401);
            }

            if($conf){
                $conf->update([
                    'title' => $request->title,
                    'content' => $request->content,
                    'version' => $request->version
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "confidentialité mis a jour",
                    'data' => $conf
                ], 201);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la mise ajour de la confidentialité: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la mis a jour de la confidentilité: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_confidentialite($id){
        try{
            $conf = LegalDocument::where('id', $id)->first();

            if(!$conf){
                return response()->json([
                    'success' => false,
                    'message' => "confidentialité introuvable!"
                ], 404);
            }

            if($conf){
                $conf->delete();
                return response()->json([
                    'success' => true,
                    'message' => "conf supprimer!"
                ], 204);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la suppression de la confidentialité: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la suppression de la confidentilité: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function publish($id){
        $document = LegalDocument::findOrFail($id);

        LegalDocument::where('type', $document->type)
            ->where('id', '!=', $id)
            ->update([
                'is_active' => false
            ]);

        $document->update([
            'is_active' => true,
            'published_at' => now()
        ]);

        return response()->json([
            'sucess' => true,
            'message' => 'Document publié avec succès'
        ]);
    }


    public function getActive($type){
        $document = LegalDocument::where('type', $type)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$document) {
            return response()->json([
                'message' => 'Document introuvable'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Liste des documents",
            'data' => $document
        ]);
    }
}
