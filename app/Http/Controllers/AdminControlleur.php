<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use GuzzleHttp\Psr7\Query;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class AdminControlleur extends Controller
{
    public function add_admin(Request $request){
        try{
            $validated = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
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

        // $path = $request->file('image')->store('image', 'public');

        if($admin->role === 'admin'){
            $info_admin = Admin::create([
                'nom' => $request->nom,
                'numero' => $request->numero,
                // 'image' => $path,
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
                    // 'image' => $info_admin->image,
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
                    'message' => "Admin connecté avec succès!!",
                    'data' => [
                        'id' => $admin->id,
                        'nom' => $admin->nom,
                        'numero' => "+225".$admin->numero,
                        'image' => asset('storage/'.$admin->image),
                        'email' => $admin->email,
                    ],
                    'token' => $token
                ]);
            }else {
                return response()->json([
                    'success' => false,
                    'message' => "Erreur de connexion!"
                ], 404);
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
            $admin = Auth::guard('admin')->user();

            if (! $admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin introuvable'
                ], 404);
            }
            if($admin->role === 'admin'){
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
            }
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

    public function update_admin_profile(Request $request){
        $validated = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'numero' => 'required|string|max:10',
            'image' => 'mimes:png,jpg,jpeg',
            'email' => 'required|string',
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }

        try{
            $admin = Auth::guard('admin')->user();
            $path = $request->file('image')->store('image', 'public');

            if(!$admin){
                return response()->json([
                    'success' => false,
                    'message' => "Admin introuvable"
                ], 401);
            }

            if($admin){
                $admin->update([
                    'nom' => $request->nom,
                    'numero' => $request->numero,
                    'image' => $path,
                    'email' => $request->email,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Mise a jour effectué avec succès",
                    'data' => [
                        'id' => $admin->id,
                        'nom' => $admin->nom,
                        'numero' => $admin->numero,
                        'image' => asset('storage/'.$admin->image),
                        'email' => $admin->email
                    ],
                ], 201);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la mise a jour de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la mise a jour de l'admin: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update_password_admin(Request $request){
        $validated = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ]);

        if($validated->fails()){
            return response()->json([
                'success' => false,
                'message' => "Erreur de validation",
                'erreur' => $validated->errors()
            ], 422);
        }

        try{
            $admin = Auth::guard('admin')->user();

            if(!$admin){
                return response()->json([
                    'success' => false,
                    'message' => "Admin nom authentifié"
                ], 401);
            }

            if(!Hash::check($request->old_password, $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => "Ancien mot de passe incorrect"
                ], 401);
            }

            $admin->password = Hash::make($request->new_password);
            $admin->save();

            return response()->json([
                'success' => true,
                'message' => "Mot de passe mis à jour avec succès"
            ], 200);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la mise a jour du mot de passe: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la mise a jour du mot de passe: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function forgot(Request $request){
        $request->validate([
            'email' => 'required|email'
        ]);

        $status = Password::broker('admins')->sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Email de réinitialisation envoyé avec succès'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Impossible d’envoyer le lien',
            'error' => $status
        ], 400);
    }

    public function showResetForm(Request $request, $token = null){
        return view('admin.reset-password', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    public function reset(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($admin, $password) {
                $admin->password = Hash::make($password);
                $admin->save();
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('admin.forgot')->with('success', 'Mot de passe changé')
            : back()->withErrors(['email' => __($status)]);
    }

    public function liste_user_admin(){
        try{
            $user = User::all()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'numero' => $user->numero,
                    'email' => $user->email,
                    'nbr_etoile' => $user->nbr_etoile
                ];
            });

            return response()->json([
                'success' => true,
                'message' => "Liste des utulisateurs",
                'data' => $user
            ], 200);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de reccupereation de la liste des utulisateurs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la reccuperation de la liste des utulisateurs: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getById_user_admin($id_user){
        try{
            $user = User::where('id', $id_user)->first();

            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => "Utulisateur introuvable",
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => "Information utulisateur trouvé",
                'data' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'numero' => $user->numero,
                    'image' => $user->image,
                    'email' => $user->email,
                    'nbr_etoile' => $user->nbr_etoile
                ]
            ], 200);
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la reccuperation des informations de l'utulisateur: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la reccuperaton des informations de l'utulisateur: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function delete_user_admin($id_user){
        try{
            $user = User::where('id', $id_user)->first();

            if(!$user){
                return response()->json([
                    'success' => false,
                    'message' => "Utulisateur introuvable",
                ], 401);
            }

            if($user){
                $user->delete();

                return response()->json([
                    'success' => true,
                    'message' => "Utulisateur supprimer"
                ], 204);
            }
        }catch(QueryException $e){
            Log::error("Erreur sql lors de la suppression de l'utulisateur: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }catch(\Exception $e){
            Log::error("Erreur serveur lors de la suppression de l'utulisateur: ". $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 