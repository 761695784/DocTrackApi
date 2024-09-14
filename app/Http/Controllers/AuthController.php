<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Inscription d'un utilisateur
     */
    public function register(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'FirstName' => 'required|string|max:40',
            'LastName' => 'required|string|max:20',
            'Adress' => 'required|string|max:100',
            'Phone' => 'required|string|max:20',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Gestion des erreurs de validation
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'inscription. Veuillez vérifier les erreurs ci-dessous.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Création de l'utilisateur
        $user = User::create([
            'FirstName' => $request->FirstName,
            'LastName' => $request->LastName,
            'Adress' => $request->Adress,
            'Phone' => $request->Phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assignation du rôle SimpleUser par défaut
        $user->assignRole('SimpleUser');

        // Génération du token JWT
        $token = JWTAuth::fromUser($user);

        // Retourner un message de succès
        return response()->json([
            'success' => true,
            'message' => 'Inscription réussie ! Bienvenue sur la plateforme.',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Connexion d'un utilisateur
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Tentative de connexion avec JWT
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Vos identifiants sont invalides. Veuillez réessayer.'
            ], 401);
        }
        $user = Auth::user();
        $roles = $user->getRoleNames(); // Récupérer le rôle

        // Connexion réussie
        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie  !',
            'user' => $user,
            'token' => $token,
            'roles' => $roles // Retourner le ou les rôles de l'utilisateur
        ], 200);
    }

    /**
     * Déconnexion de l'utilisateur (invalidation du token)
     */
    public function logout()
    {
        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie. À bientôt !'
        ], 200);
    }

    /**
     * Récupérer les informations de l'utilisateur authentifié
     */
    public function me()
    {
        $user = Auth::user();
        $roles = $user->getRoleNames(); // Récupérer le ou les rôles de l'utilisateur

        return response()->json([
            'success' => true,
            'user' => $user,
            'roles' => $roles
        ], 200);
    }


    /**
     * Rafraîchir le token
     */
    public function refresh()
    {
        return response()->json([
            'success' => true,
            'token' => Auth::refresh(),
            'message' => 'Le token a été rafraîchi avec succès.'
        ], 200);
    }

   /**
 * Modification du mot de passe de l'utilisateur
 */
public function changePassword(Request $request)
{
    // Validation des données entrées
    $validator = Validator::make($request->all(), [
        'current_password' => 'required|string|min:8',
        'new_password' => 'required|string|min:8|confirmed',
    ]);

    // Gestion des erreurs de validation
    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation. Veuillez vérifier les entrées.',
            'errors' => $validator->errors()
        ], 422);
    }

    $user = Auth::user();

    // Vérifier que le mot de passe actuel est correct
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Le mot de passe actuel est incorrect.'
        ], 400);
    }

    // Mettre à jour le mot de passe de l'utilisateur
    $user->password = Hash::make($request->new_password);
    $user->save();

    // Retourner un message de succès
    return response()->json([
        'success' => true,
        'message' => 'Votre mot de passe a été mis à jour avec succès.'
    ], 200);
}

    public function getAllUsersWithRoles()
{
    // Récupérer tous les utilisateurs avec leurs rôles
    $users = User::with('roles')->get();

    // Parcourir chaque utilisateur pour récupérer son ou ses rôles
    $usersWithRoles = $users->map(function ($user) {
        return [
            'id' => $user->id,
            'FirstName' => $user->FirstName,
            'LastName' => $user->LastName,
            'email' => $user->email,
            'roles' => $user->getRoleNames() // Récupérer les rôles
        ];
    });

    // Retourner la réponse en JSON
    return response()->json([
        'success' => true,
        'users' => $usersWithRoles
    ], 200);
}
}

