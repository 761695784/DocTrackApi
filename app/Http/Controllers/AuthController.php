<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

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


/**
 * Methode pour la liste des utilisateurs avec leurs rôles
 */
public function getAllUsersWithRoles()
{
    // Vérifier si l'utilisateur est authentifié et a le rôle 'Admin'
    if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous devez être un administrateur pour voir cette liste.'
        ], 403);
    }

    // Récupérer tous les utilisateurs avec leurs rôles
    $users = User::with('roles')->get();

    // Parcourir chaque utilisateur pour récupérer son ou ses rôles
    $usersWithRoles = $users->map(function ($user) {
        return [
            'id' => $user->id,
            'FirstName' => $user->FirstName,
            'LastName' => $user->LastName,
            'Adress' => $user->Adress,
            'Phone' => $user->Phone,
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

/**
 * Methode pour la suppression d'un user par l'admin
*/

public function deleteUser($id)
{
    // Vérifier si l'utilisateur est authentifié et a le rôle 'Admin'
    if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous devez être un administrateur pour effectuer cette action.'
        ], 403);
    }

    // Trouver l'utilisateur à supprimer
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non trouvé.'
        ], 404);
    }

    // Vérifier que l'utilisateur a le rôle 'SimpleUser'
    if ($user->hasRole('Admin')) {
        return response()->json([
            'success' => false,
            'message' => 'Vous ne pouvez pas supprimer un autre administrateur.'
        ], 403);
    }

    // Supprimer l'utilisateur s'il a le rôle 'SimpleUser'
    $user->delete();

    return response()->json([
        'success' => true,
        'message' => 'Utilisateur supprimé avec succès.'
    ], 200);
}


/**
 * Methode pour la creation d'un admin
*/

public function createAdmin(Request $request)
{
    // Vérifier si l'utilisateur est authentifié et a le rôle 'Admin'
    if (!Auth::user() || !Auth::user()->hasRole('Admin')) {
        return response()->json([
            'success' => false,
            'message' => 'Accès refusé. Vous devez être un administrateur pour effectuer cette action.'
        ], 403);
    }

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
            'message' => 'Échec de la création de l\'utilisateur. Veuillez vérifier les erreurs ci-dessous.',
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

    // Assignation du rôle Admin
    $user->assignRole('Admin');

    // Génération du token JWT
    $token = JWTAuth::fromUser($user);

    // Retourner un message de succès
    return response()->json([
        'success' => true,
        'message' => 'Utilisateur Admin créé avec succès !',
        'user' => $user,
        'token' => $token
    ], 201);
}

/**
 * Méthode pour mettre à jour les informations du profil de l'utilisateur
 */
public function updateProfile(Request $request)
{
    // Vérifier si l'utilisateur est authentifié
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être connecté pour modifier votre profil.'
        ], 401);
    }

    // Validation des données entrées
    $validator = Validator::make($request->all(), [
        'FirstName' => 'sometimes|required|string|max:40',
        'LastName' => 'sometimes|required|string|max:20',
        'Adress' => 'sometimes|required|string|max:100',
        'Phone' => 'sometimes|required|string|max:20',
        'email' => 'sometimes|required|string|email|max:50|unique:users,email,' . Auth::id(),
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

    // Mettre à jour les informations de l'utilisateur
    $user->update($request->only('FirstName', 'LastName', 'Adress', 'Phone', 'email'));

    // Retourner un message de succès
    return response()->json([
        'success' => true,
        'message' => 'Informations du profil mises à jour avec succès.',
        'user' => $user
    ], 200);
}


 /**
     * Envoyer un lien de réinitialisation de mot de passe
     */
    public function forgotPassword(Request $request)
    {
        // Validation des données
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Envoi du lien de réinitialisation
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['success' => true, 'message' => __($status)], 200)
            : response()->json(['success' => false, 'message' => __($status)], 400);
    }

    /**
     * Réinitialiser le mot de passe
     */
    public function resetPassword(Request $request)
    {
        // Validation des données
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Réinitialisation du mot de passe
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['success' => true, 'message' => __($status)], 200)
            : response()->json(['success' => false, 'message' => __($status)], 400);
    }

    /**
     * Connecter un utilisateur avec un compte Google
     */
    public function handleGoogleCallback(Request $request) {
        try {
            // Récupérer les informations de l'utilisateur Google
            $googleUser = Socialite::driver('google')->user();

            // Vérifier si l'utilisateur existe déjà dans la base de données
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                // Retourner une réponse indiquant que l'adresse et le numéro de téléphone sont requis
                return response()->json([
                    'success' => false,
                    'message' => 'Veuillez fournir votre adresse et votre numéro de téléphone pour finaliser la création du compte.',
                    'required_fields' => ['Adress', 'Phone'],
                    'google_user' => [ // Optionnel : Envoyer les infos Google pour pré-remplir le formulaire
                        'email' => $googleUser->email,
                        'first_name' => $googleUser->user['given_name'] ?? $googleUser->name,
                        'last_name' => $googleUser->user['family_name'] ?? '',
                    ],
                ], 400);
            }

            // Si l'utilisateur existe déjà, générer un token JWT
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Connexion avec Google réussie !',
                'user' => $user,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la connexion avec Google. Veuillez réessayer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Finaliser l'inscription avec Google
     */
    public function finalizeAccountCreation(Request $request) {
        try {
            // Valider les données reçues
            $request->validate([
                'email' => 'required|email',
                'Adress' => 'required|string',
                'Phone' => 'required|string',
            ]);

            // Récupérer l'utilisateur temporaire (si vous avez stocké les infos Google)
            $email = $request->input('email');
            $googleUser = Socialite::driver('google')->user(); // Optionnel : Revalider l'utilisateur

            // Vérifier si l'utilisateur existe
            $user = User::where('email', $email)->first();
            if (User::where('email', $request->input('email'))->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un utilisateur avec cet email existe déjà.',
                ], 400);
            }

            // Créer l'utilisateur
            $user = User::create([
                'FirstName' => $googleUser->user['given_name'] ?? $googleUser->name,
                'LastName' => $googleUser->user['family_name'] ?? '',
                'email' => $email,
                'password' => Hash::make(Str::random(16)), // Mot de passe aléatoire
                'Adress' => $request->input('Adress'),
                'Phone' => $request->input('Phone'),
            ]);

            // Assigner le rôle SimpleUser par défaut
            $user->assignRole('SimpleUser');

            // Générer un token JWT pour l'utilisateur
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès !',
                'user' => $user,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la création du compte. Veuillez réessayer.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}

