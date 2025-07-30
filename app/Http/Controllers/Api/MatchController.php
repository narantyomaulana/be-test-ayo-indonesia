<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Match;
use App\Models\Goal;
use App\Models\Matches;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $matches = Matches::with(['homeTeam', 'awayTeam', 'goals.player'])->get();

            return response()->json([
                'success' => true,
                'message' => 'Matches retrieved successfully',
                'data' => $matches
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'match_date' => 'required|date|after_or_equal:today',
                'match_time' => 'required|date_format:H:i',
                'home_team_id' => 'required|exists:teams,id',
                'away_team_id' => 'required|exists:teams,id|different:home_team_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $match = Matches::create($request->all());
            $match->load(['homeTeam', 'awayTeam']);

            return response()->json([
                'success' => true,
                'message' => 'Match scheduled successfully',
                'data' => $match
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $match = Matches::with(['homeTeam', 'awayTeam', 'goals.player'])->find($id);

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Match retrieved successfully',
                'data' => $match
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $match = Matches::find($id);

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'match_date' => 'sometimes|required|date',
                'match_time' => 'sometimes|required|date_format:H:i',
                'home_team_id' => 'sometimes|required|exists:teams,id',
                'away_team_id' => 'sometimes|required|exists:teams,id|different:home_team_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $match->update($request->all());
            $match->load(['homeTeam', 'awayTeam']);

            return response()->json([
                'success' => true,
                'message' => 'Match updated successfully',
                'data' => $match
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $match = Matches::find($id);

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found'
                ], 404);
            }

            $match->delete();

            return response()->json([
                'success' => true,
                'message' => 'Match deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete match',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateResult(Request $request, $id): JsonResponse
    {
        try {
            $match = Matches::find($id);

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'home_score' => 'required|integer|min:0',
                'away_score' => 'required|integer|min:0',
                'goals' => 'required|array',
                'goals.*.player_id' => 'required|exists:players,id',
                'goals.*.minute' => 'required|integer|min:1|max:120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $totalGoals = count($request->goals);
            $totalScore = $request->home_score + $request->away_score;

            if ($totalGoals !== $totalScore) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total goals must match total score'
                ], 422);
            }

            DB::transaction(function () use ($match, $request) {
                $match->update([
                    'home_score' => $request->home_score,
                    'away_score' => $request->away_score,
                    'status' => 'completed'
                ]);

                $match->goals()->delete();

                foreach ($request->goals as $goalData) {
                    Goal::create([
                        'match_id' => $match->id,
                        'player_id' => $goalData['player_id'],
                        'minute' => $goalData['minute']
                    ]);
                }
            });

            $match->load(['homeTeam', 'awayTeam', 'goals.player']);

            return response()->json([
                'success' => true,
                'message' => 'Match result updated successfully',
                'data' => $match
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update match result',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
