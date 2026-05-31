<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class AssistanceController extends Controller
{
    public function create_assistance(Request $request)
    {
        try {

            $validated = Validator::make($request->all(), [
                'type' => 'required|in:telephone,whatsapp',
                'contact' => 'required|string',
                'description' => 'nullable|string'
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation!",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $assistance = Assistance::create([
                'type' => $request->type,
                'contact' => $request->contact,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => "Assistance créée avec succès",
                'data' => [
                    'id' => $assistance->id,
                    'type' => $assistance->type,
                    'contact' => $assistance->contact,
                    'description' => $assistance->description
                ]
            ], 201);

        } catch (QueryException $e) {
            Log::error("Erreur SQL create assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error("Erreur serveur create assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function liste_assistance()
    {
        try {

            $assistances = Assistance::all()->map(function ($a) {
                return [
                    'id' => $a->id,
                    'type' => $a->type,
                    'contact' => $a->contact,
                    'description' => $a->description
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Liste des assistances",
                'data' => $assistances
            ], 200);

        } catch (QueryException $e) {
            Log::error("Erreur SQL liste assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error("Erreur serveur liste assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_assistance(Request $request, $id)
    {
        try {

            $validated = Validator::make($request->all(), [
                'type' => 'required|in:telephone,whatsapp',
                'contact' => 'required|string',
                'description' => 'nullable|string'
            ]);

            if ($validated->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation!",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $assistance = Assistance::where('id', $id)->first();

            if (!$assistance) {
                return response()->json([
                    'success' => false,
                    'message' => "Assistance introuvable!"
                ], 404);
            }

            $assistance->update([
                'type' => $request->type,
                'contact' => $request->contact,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => "Assistance mise à jour avec succès",
                'data' => [
                    'id' => $assistance->id,
                    'type' => $assistance->type,
                    'contact' => $assistance->contact,
                    'description' => $assistance->description
                ]
            ]);

        } catch (QueryException $e) {
            Log::error("Erreur SQL update assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error("Erreur serveur update assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_assistance($id)
    {
        try {

            $assistance = Assistance::where('id', $id)->first();

            if (!$assistance) {
                return response()->json([
                    'success' => false,
                    'message' => "Assistance introuvable!"
                ], 404);
            }

            $assistance->delete();

            return response()->json([
                'success' => true,
                'message' => "Assistance supprimée avec succès"
            ], 200);

        } catch (QueryException $e) {
            Log::error("Erreur SQL delete assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            Log::error("Erreur serveur delete assistance: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}