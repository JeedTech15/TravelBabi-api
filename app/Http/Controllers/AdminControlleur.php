<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use GuzzleHttp\Psr7\Query;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminControlleur extends Controller
{
    public function add_admin(Request $request){
        try{
            $validated = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'image' => 'required|mimes:png,jpg,jpeg',
            'email' => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => true,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }

        $admin = Auth::guard('admin')->user();

        if($admin->role === 'sous_admin'){
            return response()->json([
                'success' => false,
                'message' => "Vous n'etes pas autorisé a ajouter un admin"
            ], 404);
        }

        $path = $request->file('image')->store('image', 'public');

        if($admin->role === 'admin'){
            $info_admin = Admin::create([
                'nom' => $request->nom,
                'numero' => $request->numero,
                'image' => $path,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'sous_admin'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Admin ajouter avec succes!",
                'data' => [
                    'id' => $info_admin->id,
                    'nom' => $info_admin->nom,
                    'numero' => "+225".$info_admin->numero,
                    'image' => $info_admin->image,
                    'email' => $info_admin->email,
                    'password' => $info_admin->password,
                    'role' => $info_admin->role
                ]
            ]);
        };
        }catch(QueryException $e){
            Log::error("Erreur sql lors le l'ajout de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la l'ajout de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function login_admin(Request $request){
        try{
            $validated = Validator::make($request->all(), [
                'email' => 'required|string',
                'password' => 'required|string|min:8'
            ]);

            if($validated->fails()){
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de validation",
                    'erreur' => $validated->errors()
                ], 422);
            }

            $admin = Admin::where('email', $request->email)->first();

            if($admin && Hash::check($request->password, $admin->password)){
                $token = $admin->createToken('auth:admin')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => "Admin connecté avec succès!",
                    'data' => [
                        'id' => $admin->id,
                        'nom' => $admin->nom,
                        'numero' => "+225".$admin->numero,
                        'image' => $admin->image,
                        'email' => $admin->email,
                    ],
                    'token' => $token
                ]);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la connexion de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la connecion de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_profil_admin(Request $request){
        try {
        $validated = Validator::make($request->all(), [
            'nom'      => 'required|string|max:255',
            'numero'   => 'required|string|max:10',
            'image'    => 'required|mimes:png,jpg,jpeg',
            'email'    => 'required|string',
            'password' => 'required|string|min:8'
        ]);

        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur'  => $validated->errors()
            ], 422);
        }

        /** @var \App\Models\Admin|null $admin */
        $admin = Auth::guard('admin')->user();


        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin introuvable'
            ], 404);
        }


        if ($admin->role === 'admin') {
            return response()->json([
                'success' => false,
                'message' => "Vous n'etes pas autorisé a modifier le profil"
            ], 403);
        }

        $path = $request->file('image')->store('image', 'public');

        if ($admin->role === 'sous_admin') {

            $admin->update([
                'nom'      => $request->nom,
                'numero'   => $request->numero,
                'image'    => $path,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'sous_admin'
            ]);

            return response()->json([
                'success' => true,
                'message' => "Profil modifié avec succès !",
                'data'    => [
                    'id'     => $admin->id,
                    'nom'    => $admin->nom,
                    'numero' => "+225" . $admin->numero,
                    'image'  => $admin->image,
                    'email'  => $admin->email,
                    'role'   => $admin->role
                ]
            ], 200);
        }

    }catch (QueryException $e) {

        Log::error("Erreur SQL lors de la modification de l'admin : " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Erreur base de données'
        ], 500);

    }catch (\Exception $e) {

        Log::error("Erreur serveur lors de la modification de l'admin : " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur'
        ], 500);
    }
}

    public function delete_admin(Request $request,$id_admin){
        try{
            $admin_connect = Auth::guard('admin')->user();
            $admin = Admin::where('id', $id_admin)->first();

            if(!$admin){
                return response()->json([
                    'success' => false,
                    'message' => "Cet admin n'a pas ete trouvé"
                ], 404);
            }

            if($admin && $admin_connect->role === 'admin'){
                $admin->delete();
                return response()->json([
                    'success' => true,
                    'message' => "Admin supprimer avec succès!",
                ], 204);
            }
        }catch(QueryException $e){
            Log::error("Erreur serveur lors de la suppression de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la suppression de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function liste_admin(){
        try{
            $admins = Admin::where('role', 'sous_admin')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'nom' => $admin->nom,
                    'numero' => $admin->numero,
                    'image' => $admin->image,
                    'email' => $admin->email,
                    'role' => $admin->role,
                    'created_at' => $admin->created_at,
                ];
            });
            return response()->json([
                'success' => true,
                'message' => "Liste des admins !",
                'data' => $admins
            ]);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la reccuperation de la liste des admins: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la reccuperation de la liste des admins: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}