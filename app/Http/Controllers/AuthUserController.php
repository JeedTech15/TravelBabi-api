<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AuthUserController extends Controller
{
    public function auth_user(Request $request){
        $validator = Validator::make($request->all(), [
            'numero' => 'required|digits:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $code_otp = substr($request->numero, -4);
            $utilisateur = User::where('numero', $request->numero)->first();

            if ($utilisateur) {

                if ($utilisateur->otp != null && $utilisateur->expires_otp_at > now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Un code OTP a déjà été envoyé'
                    ], 400);
                }

                $utilisateur->otp = $code_otp;
                $utilisateur->expires_otp_at = now()->addMinutes(10);
                $utilisateur->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Utilisateur connecté. Un code OTP a été envoyé.'
                ], 200);
            }

            $user = new User();
            $user->numero = $request->numero;
            $user->otp = $code_otp;
            $user->expires_otp_at = now()->addMinutes(10);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur inscrit. Un code OTP a été envoyé.'
            ], 200);

        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’ajout d’un utilisateur',
                'erreur' => $e->getMessage()
            ], 500);
        }
    }


    public function verify_otp(Request $request){
        $validator = Validator::make($request->all(), [
            'numero' => 'required|digits:10',
            'code_otp' => 'required|size:4'
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $user = User::where('numero', $request->numero)->first();
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable'
                ],404);
            }
            if ($user->otp && hash_equals($user->otp, $request->code_otp) && Carbon::parse($user->expires_otp_at)->isFuture()) {
                $user->otp = null;
                $user->expires_otp_at = null;
                $user->is_verified = true;
                $user->save();

                $token = $user->createToken('token_user')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $user->id,
                        'nom' => $user->nom,
                        'email' => $user->email,
                        'numero' => $user->numero,
                        'image' => $user->image,
                        'nbr_etoile' => $user->nbr_etoile,
                        'token' => $token
                    ],
                    'message' => 'Utilisateur vérifié avec succès'
                ],200);
            }
            return response()->json([
                'success' => false,
                'message' => 'OTP incorrect ou temps passé. Veuillez réésayez de vous connecter.'
            ],400);
        } 
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du code otp',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    public function renvoyer_otp(Request $request){
        $validator = Validator::make($request->all(), [
            'numero' => 'required|digits:10',
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }
        try{
            $code_otp = substr($request->numero, -4);
            $user = User::where('numero', $request->numero)->first();
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable'
                ],404);
            }
            $user->otp = $code_otp;
            $user->expires_otp_at = now()->addMinutes(10);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'OTP renvoyé avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du renvoi du code otp',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    public function info_user(Request $request){
        try{
            $user = User::find($request->user()->id);
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable.'
                ],404);
            }

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
                'message' => 'Informations de l’utilisateur affichée avec succès'
            ],200);
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l’affichage des infos de l’utilisateur',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    public function update_info_user(Request $request){
        $validator = Validator::make($request->all(), [
            'nom' => 'nullable',
            'email' => 'nullable|email',
            'image' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);
        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ],422);
        }

        try{
            $user = User::find($request->user()->id);
            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur introuvable'
                ],404);
            }
            $image = null;

            if ($request->hasFile('image')) {
                $image = $this->uploadImageToHosting($request->file('image'));
            }

            $user->nom = $request->nom ?? $user->nom;
            $user->email = $request->email ?? $user->email;
            $user->image = $image ?? $user->image;
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
                'message' => 'Informations de l’utilisateur modifiee avec succès'
            ],200);
            
        }
        catch(QueryException $e){
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil de l’utilisateur',
                'erreur' => $e->getMessage()
            ],500);
        }
    }

    private function uploadImageToHosting($image){
        $apiKey = '9b1ab6564d99aab6418ad53d3451850b';

        // Vérifie que le fichier est une instance valide
        if (!$image->isValid()) {
            throw new \Exception("Fichier image non valide.");
        }

        // Lecture et encodage en base64
        $imageContent = base64_encode(file_get_contents($image->getRealPath()));

        /** @var Response $response */
        $response = Http::asForm()->post('https://api.imgbb.com/1/upload', [
            'key' => $apiKey,
            'image' => $imageContent,
        ]);

        if ($response->successful()) {
            return $response->json()['data']['url'];
        }

        throw new \Exception("Erreur lors de l'envoi de l'image : " . $response->body());
    }
    
}
