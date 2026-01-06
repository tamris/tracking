<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\RequirementApiController;
use App\Http\Controllers\Api\DesignSpecApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Public Routes (Auth) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// --- Protected Routes (Perlu Token) ---
Route::middleware('auth:sanctum')->group(function () {

    // User Info
    Route::get('/me', fn(Request $request) => $request->user());

    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout sukses']);
    });

    Route::get('/dashboard-stats', [DashboardApiController::class, 'index']);

    // --- Project Management ---
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::post('/projects/{id}', [ProjectController::class, 'update']); // Method spoofing untuk file upload
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);

    // --- Requirements (Nested dalam Projects) ---
    // URL: /api/projects/{project}/requirements
    Route::apiResource('projects.requirements', RequirementApiController::class)
        ->scoped(['project' => 'id', 'requirement' => 'id']);

    // --- Design Specs (Nested dalam Projects) ---
    // URL: /api/projects/{project}/design-specs
    Route::apiResource('projects.design-specs', DesignSpecApiController::class)
        ->scoped(['project' => 'id', 'design_spec' => 'id']);

    Route::apiResource('projects.developments', \App\Http\Controllers\Api\DevelopmentApiController::class)
        ->scoped(['project' => 'id', 'development' => 'id']);

    Route::apiResource('projects.test-cases', \App\Http\Controllers\Api\TestCaseApiController::class)
        ->scoped(['project' => 'id', 'test_case' => 'id']);

    Route::apiResource('projects.deployments', \App\Http\Controllers\Api\DeploymentApiController::class)
        ->scoped(['project' => 'id', 'deployment' => 'id']);

    Route::apiResource('projects.maintenances', \App\Http\Controllers\Api\MaintenanceApiController::class)
        ->scoped(['project' => 'id', 'maintenance' => 'id']);

    Route::get('/projects/{project}/planning', [\App\Http\Controllers\Api\PlanningApiController::class, 'index']);

    // POST Update Planning (Activity, Note, & Upload Files)
    Route::post('/projects/{project}/planning', [\App\Http\Controllers\Api\PlanningApiController::class, 'update']);

    // DELETE Planning File
    Route::delete('/projects/{project}/planning/files/{file}', [\App\Http\Controllers\Api\PlanningApiController::class, 'destroyFile']);

    Route::get('/reports', [\App\Http\Controllers\Api\ReportApiController::class, 'index']);

    Route::get('/profile', [\App\Http\Controllers\Api\ProfileApiController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\Api\ProfileApiController::class, 'update']);
});
