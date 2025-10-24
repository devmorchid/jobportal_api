<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ApplicationController;

// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/employer', [AuthController::class, 'registerEmployer']);

// Get authenticated user
Route::middleware(['auth:sanctum', 'role:employer|admin'])
    ->get('/test-role-check', function () {
        return auth()->user()->getRoleNames();
    });




Route::middleware('auth:sanctum')->group(function () {
    Route::post('/jobs', [JobController::class, 'store']);
    Route::put('/jobs/{id}', [JobController::class, 'update']);
    Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
});


// Jobs routes
Route::middleware(['auth:sanctum'])->group(function () {
    // routes accessible for all authenticated users
    Route::get('/jobs', [JobController::class, 'index']);
    Route::get('/jobs/{id}', [JobController::class, 'show']);

    // routes restricted to employer or admin
    Route::middleware(['role:employer|admin'])->group(function () {
        Route::post('/jobs', [JobController::class, 'store']);
        Route::put('/jobs/{id}', [JobController::class, 'update']);
        Route::delete('/jobs/{id}', [JobController::class, 'destroy']);
    });
});



// // Applications routes
// Route::middleware('auth:sanctum')->group(function () {

//     // user
//     Route::post('/jobs/{job_id}/apply', [ApplicationController::class, 'store']);
//     Route::get('/my-applications', [ApplicationController::class, 'myApplications']);

//     // employer
//     Route::middleware('role:employer|admin')->group(function () {
//         Route::get('/employer/applications', [ApplicationController::class, 'employerApplications']);
//     });

//     // admin
//     Route::middleware('role:admin')->group(function () {
//         Route::get('/applications', [ApplicationController::class, 'index']);
//     });
// });

// Route::get('/jobs/search', [JobController::class, 'search']);


