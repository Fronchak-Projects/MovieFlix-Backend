<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('genres', GenreController::class);
Route::apiResource('movies', MovieController::class);
Route::group([
    'prefix' => 'users'
], function() {
    Route::get('', [UserController::class, 'index']);
    Route::get('me', [UserController::class, 'me']);
    Route::get('{id}', [UserController::class, 'show']);
    Route::put('update', [UserController::class, 'update']);
    Route::delete('{id}', [UserController::class, 'destroy']);
    Route::put('{id}/roles', [UserController::class, 'updateRoles']);
});

Route::post('movies/{movieId}/reviews', [ReviewController::class, 'store']);
Route::get('movies/{movieId}/reviews', [ReviewController::class, 'movieReviews']);
Route::get('users/{userId}/reviews', [ReviewController::class, 'userReviews']);

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function() {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('register', [AuthController::class, 'register']);
    Route::put('change-password', [AuthController::class, 'changePassword']);
});
