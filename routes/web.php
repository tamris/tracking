<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DesignSpecController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\DevelopmentController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\TestCaseController;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'))->name('root');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login',  [AuthController::class, 'loginPage'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register',  [AuthController::class, 'registerPage'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    Route::get('/login/google', [AuthController::class, 'redirectToGoogle'])
        ->name('login.google');

    Route::get('/login/google/callback', [AuthController::class, 'handleGoogleCallback'])
        ->name('login.google.callback');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /* ================= DASHBOARD ================= */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /* ================= PROFILE ================= */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::view('/notifications', 'notifications.index')
        ->name('notifications.index');

    /* ================= PROJECT ================= */
    Route::resource('projects', ProjectController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::get('/projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show');

    /* ================= SDLC VIEW ================= */
    Route::get(
        '/projects/{project}/sdlc/{phase}',
        [ProjectController::class, 'showPhase']
    )
    ->whereIn('phase', [
        'planning',
        'requirement',
        'design',
        'development',
        'testing',
        'deployment',
        'maintenance',
    ])
    ->name('projects.sdlc');

    /* ================= PLANNING ================= */
    Route::put(
        '/projects/{project}/sdlc/planning',
        [PlanningController::class, 'update']
    )->name('projects.planning.update');

    Route::delete(
        '/project-files/{file}',
        [PlanningController::class, 'destroyFile']
    )->name('project-files.destroy');

    /* ================= REQUIREMENT ================= */
    Route::post(
        '/projects/{project}/requirements',
        [RequirementController::class, 'store']
    )->name('projects.requirements.store');

    Route::put(
        '/projects/{project}/requirements/{requirement}',
        [RequirementController::class, 'update']
    )->name('projects.requirements.update');

    Route::delete(
        '/projects/{project}/requirements/{requirement}',
        [RequirementController::class, 'destroy']
    )->name('projects.requirements.destroy');

    /* ================= DESIGN ================= */
    Route::resource(
        'projects.design-specs',
        DesignSpecController::class
    )->only(['store', 'update', 'destroy'])->shallow();

    /* ================= DEVELOPMENT ================= */
    Route::resource(
        'projects.developments',
        DevelopmentController::class
    )->only(['store', 'update', 'destroy'])->shallow();

    /* ================= TESTING ================= */
    Route::resource(
        'projects.test-cases',
        TestCaseController::class
    )->only(['store', 'update', 'destroy'])->shallow();
    Route::put('/test-cases/{test_case}', [TestCaseController::class, 'update'])->name('test-cases.update');


    /* ================= DEPLOYMENT ================= */
    Route::resource(
        'projects.deployments',
        DeploymentController::class
    )->only(['store', 'update', 'destroy'])->shallow();

    /* ================= MAINTENANCE ================= */
    Route::resource(
        'projects.maintenances',
        MaintenanceController::class
    )->only(['store', 'update', 'destroy'])->shallow();

    /* ================= CONTACT ================= */
    Route::get('/contact', [ContactController::class, 'index'])
        ->name('contact');

    Route::post('/contact', [ContactController::class, 'store'])
        ->name('contact.store');

    /* ================= REPORT ================= */
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    /* ================= LOGOUT ================= */
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
