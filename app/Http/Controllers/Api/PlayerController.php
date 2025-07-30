<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $players = Player::with('team')->get();

            return response()->json([
                'success' => true,
                'message' => 'Players retrieved successfully',
                'data' => $players
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve players',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'team_id' => 'required|exists:teams,id',
                'name' => 'required|string|max:255',
                'height' => 'required|integer|min:100|max:250',
                'weight' => 'required|integer|min:40|max:150',
                'position' => 'required|in:penyerang,gelandang,bertahan,penjaga_gawang',
                'jersey_number' => 'required|integer|min:1|max:99'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $existingPlayer = Player::where('team_id', $request->team_id)
                ->where('jersey_number', $request->jersey_number)
                ->first();

            if ($existingPlayer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jersey number already exists in this team'
                ], 422);
            }

            $player = Player::create($request->all());
            $player->load('team');

            return response()->json([
                'success' => true,
                'message' => 'Player created successfully',
                'data' => $player
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create player',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $player = Player::with('team')->find($id);

            if (!$player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Player retrieved successfully',
                'data' => $player
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve player',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $player = Player::find($id);

            if (!$player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'team_id' => 'sometimes|required|exists:teams,id',
                'name' => 'sometimes|required|string|max:255',
                'height' => 'sometimes|required|integer|min:100|max:250',
                'weight' => 'sometimes|required|integer|min:40|max:150',
                'position' => 'sometimes|required|in:penyerang,gelandang,bertahan,penjaga_gawang',
                'jersey_number' => 'sometimes|required|integer|min:1|max:99'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->has('jersey_number') || $request->has('team_id')) {
                $teamId = $request->team_id ?? $player->team_id;
                $jerseyNumber = $request->jersey_number ?? $player->jersey_number;

                $existingPlayer = Player::where('team_id', $teamId)
                    ->where('jersey_number', $jerseyNumber)
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingPlayer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jersey number already exists in this team'
                    ], 422);
                }
            }

            $player->update($request->all());
            $player->load('team');

            return response()->json([
                'success' => true,
                'message' => 'Player updated successfully',
                'data' => $player
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update player',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $player = Player::find($id);

            if (!$player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found'
                ], 404);
            }

            $player->delete();

            return response()->json([
                'success' => true,
                'message' => 'Player deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete player',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
