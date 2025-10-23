<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{   
    /**
* @OA\Get(
*     path="/api/applications",
*     summary="Liste des candidatures (Admin uniquement)",
*     @OA\Response(response=200, description="OK")
* )

 */
    
    // user يترشح لوظيفة
    public function store(Request $request, $job_id)
    {
        $user = Auth::user();

        $job = Job::findOrFail($job_id);

        // تأكد أنه ما ترشحش قبل
        if (Application::where('user_id', $user->id)->where('job_id', $job_id)->exists()) {
            return response()->json(['message' => 'Déjà postulé à cette offre'], 409);
        }

        $application = Application::create([
            'user_id' => $user->id,
            'job_id' => $job_id,
            'cover_letter' => $request->cover_letter,
        ]);

        return response()->json(['message' => 'Candidature envoyée', 'application' => $application], 201);
    }

    // user يشوف ترشحاته
    public function myApplications()
    {
        $user = Auth::user();
        return $user->applications()->with('job')->get();
    }

    // employer يشوف الترشيحات اللي جاو على الوظائف ديالو
    public function employerApplications()
    {
        $user = Auth::user();

        if (!$user->hasRole('employer') && !$user->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $applications = Application::whereHas('job', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with('user', 'job')->get();

        return $applications;
    }

    // admin يشوف جميع الترشيحات
    public function index()
    {
        $user = Auth::user();

        if (!$user->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return Application::with('user', 'job')->get();
    }
    
}
