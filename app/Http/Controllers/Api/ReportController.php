<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matches;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function matchReport($matchId): JsonResponse
    {
        try {
            $match = Matches::with(['homeTeam', 'awayTeam', 'goals.player'])->find($matchId);

            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found'
                ], 404);
            }

            if ($match->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not completed yet'
                ], 400);
            }

            $topScorer = $match->goals()
                ->select('player_id', DB::raw('COUNT(*) as goal_count'))
                ->groupBy('player_id')
                ->orderBy('goal_count', 'desc')
                ->first();

            $topScorerData = null;
            if ($topScorer) {
                $player = Player::with('team')->find($topScorer->player_id);
                $topScorerData = [
                    'player_name' => $player->name,
                    'team_name' => $player->team->name,
                    'goals' => $topScorer->goal_count
                ];
            }

            $homeWins = Matches::where('status', 'completed')
                ->where('match_date', '<=', $match->match_date)
                ->where(function ($query) use ($match) {
                    $query->where(function ($q) use ($match) {
                        $q->where('home_team_id', $match->home_team_id)
                          ->whereColumn('home_score', '>', 'away_score');
                    })->orWhere(function ($q) use ($match) {
                        $q->where('away_team_id', $match->home_team_id)
                          ->whereColumn('away_score', '>', 'home_score');
                    });
                })
                ->count();

            $awayWins = Matches::where('status', 'completed')
                ->where('match_date', '<=', $match->match_date)
                ->where(function ($query) use ($match) {
                    $query->where(function ($q) use ($match) {
                        $q->where('home_team_id', $match->away_team_id)
                          ->whereColumn('home_score', '>', 'away_score');
                    })->orWhere(function ($q) use ($match) {
                        $q->where('away_team_id', $match->away_team_id)
                          ->whereColumn('away_score', '>', 'home_score');
                    });
                })
                ->count();

            $matchStatus = 'Draw';
            if ($match->home_score > $match->away_score) {
                $matchStatus = 'Tim Home Menang';
            } elseif ($match->away_score > $match->home_score) {
                $matchStatus = 'Tim Away Menang';
            }

            $report = [
                'jadwal_pertandingan' => [
                    'tanggal' => $match->match_date->format('Y-m-d'),
                    'waktu' => $match->match_time->format('H:i'),
                ],
                'tim_home' => $match->homeTeam->name,
                'tim_away' => $match->awayTeam->name,
                'skor_akhir' => $match->home_score . ' - ' . $match->away_score,
                'status_akhir_pertandingan' => $matchStatus,
                'pemain_pencetak_gol_terbanyak' => $topScorerData,
                'akumulasi_total_kemenangan_tim_home' => $homeWins,
                'akumulasi_total_kemenangan_tim_away' => $awayWins,
                'detail_gol' => $match->goals->map(function ($goal) {
                    return [
                        'pemain' => $goal->player->name,
                        'tim' => $goal->player->team->name,
                        'menit' => $goal->minute
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'message' => 'Match report retrieved successfully',
                'data' => $report
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve match report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function teamStatistics(): JsonResponse
    {
        try {
            $teams = Team::all();

            $statistics = $teams->map(function ($team) {
                $homeWins = Matches::where('home_team_id', $team->id)
                    ->where('status', 'completed')
                    ->whereColumn('home_score', '>', 'away_score')
                    ->count();

                $awayWins = Matches::where('away_team_id', $team->id)
                    ->where('status', 'completed')
                    ->whereColumn('away_score', '>', 'home_score')
                    ->count();

                $homeDraws = Matches::where('home_team_id', $team->id)
                    ->where('status', 'completed')
                    ->whereColumn('home_score', '=', 'away_score')
                    ->count();

                $awayDraws = Matches::where('away_team_id', $team->id)
                    ->where('status', 'completed')
                    ->whereColumn('away_score', '=', 'home_score')
                    ->count();

                $homeLosses = Matches::where('home_team_id', $team->id)
                    ->where('status', 'completed')
                    ->whereColumn('home_score', '<', 'away_score')
                    ->count();

                $awayLosses = Matches::where('away_team_id', $team->id)
                    ->where('status', 'completed')
                    ->whereColumn('away_score', '<', 'home_score')
                    ->count();

                $totalMatches = Matches::where('status', 'completed')
                    ->where(function ($query) use ($team) {
                        $query->where('home_team_id', $team->id)
                              ->orWhere('away_team_id', $team->id);
                    })
                    ->count();

                return [
                    'team_name' => $team->name,
                    'total_wins' => $homeWins + $awayWins,
                    'total_draws' => $homeDraws + $awayDraws,
                    'total_losses' => $homeLosses + $awayLosses,
                    'total_matches' => $totalMatches
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Team statistics retrieved successfully',
                'data' => $statistics
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve team statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function topScorers(): JsonResponse
    {
        try {
            $topScorers = Player::select('players.*', DB::raw('COUNT(goals.id) as total_goals'))
                ->leftJoin('goals', 'players.id', '=', 'goals.player_id')
                ->with('team')
                ->groupBy('players.id')
                ->orderBy('total_goals', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Top scorers retrieved successfully',
                'data' => $topScorers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top scorers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
