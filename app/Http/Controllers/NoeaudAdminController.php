<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoeaudAdminController extends Controller
{
    public function create_noeud_admin(Request $request){
        $validated = Validator::make($request->all(), [
            'longitude' => 'required|double',
            'lagitude' => 'required|double'
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }
    }
}
