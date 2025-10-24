<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/jobs",
     *     tags={"Jobs"},
     *     summary="Liste des offres d'emploi",
     *     description="Retourne toutes les offres d'emploi. Les utilisateurs normaux voient seulement certains champs.",
     *     @OA\Response(response=200, description="Succès")
     * )
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return Job::all();
        }

        if ($user->hasRole('employer')) {
            return Job::where('user_id', $user->id)->get();
        }

        return Job::select('id', 'title', 'description', 'location', 'company')->get();
    }

    /**
     * @OA\Post(
     *     path="/api/jobs",
     *     tags={"Jobs"},
     *     summary="Créer une nouvelle offre d'emploi",
     *     description="Créer un job (seulement employer ou admin)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","description","location","company"},
     *             @OA\Property(property="title", type="string", example="Développeur Laravel"),
     *             @OA\Property(property="description", type="string", example="Description du job"),
     *             @OA\Property(property="location", type="string", example="Casablanca"),
     *             @OA\Property(property="company", type="string", example="TechCorp")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Job créé avec succès"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('employer') && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'company' => 'required|string|max:255',
        ]);

        $validated['user_id'] = $user->id;

        $job = Job::create($validated);

        return response()->json([
            'message' => 'Job created successfully',
            'job' => $job
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Afficher une offre d'emploi",
     *     description="Retourne les détails d'un job spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Succès"),
     *     @OA\Response(response=404, description="Job non trouvé")
     * )
     */
    public function show($id)
    {
        return Job::findOrFail($id);
    }

    /**
     * @OA\Put(
     *     path="/api/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Modifier une offre d'emploi",
     *     description="Mettre à jour un job (seulement owner ou admin)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Développeur Laravel"),
     *             @OA\Property(property="description", type="string", example="Description modifiée"),
     *             @OA\Property(property="location", type="string", example="Rabat"),
     *             @OA\Property(property="company", type="string", example="TechCorp")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Job mis à jour avec succès"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     @OA\Response(response=404, description="Job non trouvé")
     * )
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $job = Job::findOrFail($id);

        if ($user->id !== $job->user_id && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string',
            'company' => 'sometimes|string|max:255',
        ]);

        $job->update($validated);

        return response()->json([
            'message' => 'Job updated successfully',
            'job' => $job
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/jobs/{id}",
     *     tags={"Jobs"},
     *     summary="Supprimer une offre d'emploi",
     *     description="Supprime un job (seulement owner ou admin)",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Job supprimé avec succès"),
     *     @OA\Response(response=403, description="Non autorisé"),
     *     @OA\Response(response=404, description="Job non trouvé")
     * )
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $job = Job::findOrFail($id);

        if ($user->id !== $job->user_id && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job->delete();
        return response()->json(['message' => 'Job deleted successfully']);
    }

    /**
     * @OA\Get(
     *     path="/api/jobs/search",
     *     tags={"Jobs"},
     *     summary="Rechercher des offres d'emploi",
     *     description="Recherche par title, location ou company",
     *     @OA\Parameter(
     *         name="title",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="company",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Résultats de recherche")
     * )
     */
    public function search(Request $request)
    {
        $query = Job::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%'.$request->title.'%');
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%'.$request->location.'%');
        }

        if ($request->has('company')) {
            $query->where('company', 'like', '%'.$request->company.'%');
        }

        return $query->get();
    }
}
