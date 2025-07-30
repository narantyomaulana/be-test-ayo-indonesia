<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\ReportController;


Route::post('login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // Teams routes
    Route::get('teams', [TeamController::class, 'index']);
    Route::post('teams', [TeamController::class, 'store']);
    Route::get('teams/{id}', [TeamController::class, 'show']);
    Route::put('teams/{id}', [TeamController::class, 'update']);
    Route::delete('teams/{id}', [TeamController::class, 'destroy']);

    // Players routes
    Route::get('players', [PlayerController::class, 'index']);
    Route::post('players', [PlayerController::class, 'store']);
    Route::get('players/{id}', [PlayerController::class, 'show']);
    Route::put('players/{id}', [PlayerController::class, 'update']);
    Route::delete('players/{id}', [PlayerController::class, 'destroy']);

    // Matches routes
    Route::get('matches', [MatchController::class, 'index']);
    Route::post('matches', [MatchController::class, 'store']);
    Route::get('matches/{id}', [MatchController::class, 'show']);
    Route::put('matches/{id}', [MatchController::class, 'update']);
    Route::delete('matches/{id}', [MatchController::class, 'destroy']);
    Route::put('matches/{id}/result', [MatchController::class, 'updateResult']);

    // Reports routes
    Route::get('reports/match/{id}', [ReportController::class, 'matchReport']);
    Route::get('reports/team-statistics', [ReportController::class, 'teamStatistics']);
    Route::get('reports/top-scorers', [ReportController::class, 'topScorers']);
});
