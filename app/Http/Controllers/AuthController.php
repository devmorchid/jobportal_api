<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Créer un compte utilisateur (user par défaut).",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Utilisateur créé avec succès ✅"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="1|abcde12345token")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erreur de validation")
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('user');
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur créé avec succès ✅',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/register/employer",
     *     tags={"Authentication"},
     *     summary="Register a new employer",
     *     description="Créer un compte employeur avec le rôle employer.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="Company HR"),
     *             @OA\Property(property="email", type="string", example="hr@company.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Employeur créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Employeur créé avec succès ✅"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string", example="1|abcde12345token")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erreur de validation")
     * )
     */
    public function registerEmployer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('employer');
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Employeur créé avec succès ✅',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Connecter un utilisateur et renvoyer un token Sanctum",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|abcde12345token"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Identifiants invalides")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'roles' => $user->getRoleNames()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout user",
     *     description="Déconnecter l'utilisateur et supprimer son token",
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie ✅")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie ✅'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Authentication"},
     *     summary="Get current user info",
     *     description="Récupérer les informations de l'utilisateur connecté",
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
}
