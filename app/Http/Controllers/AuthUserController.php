<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthUserController extends Controller
{
    public function register_user(Request $request){
        $validator = Validator::make($request->all(),[
            'numero' => 'required|digits:10|integer' 
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => true,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{    
            $code_otp = substr($request->numero, -4);
            $user = new User();
            $user->numero = $request->numero;
            $user->otp = $code_otp;
            $user->expires_otp_at = now()->addMinutes(10);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur inscrit. Un code OTP a été envoyé.'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => true,
                'message' => 'Erreur lors de l’ajout d’un utilisateur',
                'erreur' => $e->getMessage()
            ],500);
        }    
    }

    public function verify_otp(Request $request){
        
    }
}
