<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
| These routes handle user authentication using JWT.
| All routes are prefixed with /auth
| The "api" middleware enables JSON-based API handling
*/
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {

    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    | These routes do NOT require authentication
    */

    // Register a new user
    Route::post('/register', [AuthController::class, 'register']);

    // Login user and generate JWT token
    Route::post('/login',    [AuthController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    | These routes require a valid JWT token
    | auth:api middleware validates the token
    */

    // Logout user and invalidate JWT token
    Route::post('/logout',   [AuthController::class, 'logout'])
        ->middleware('auth:api');

    // Refresh JWT token
    Route::post('/refresh',  [AuthController::class, 'refresh'])
        ->middleware('auth:api');

    // Get authenticated user profile
    Route::post('/profile',  [AuthController::class, 'profile'])
        ->middleware('auth:api');
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
