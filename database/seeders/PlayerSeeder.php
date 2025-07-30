<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\Team;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();

        foreach ($teams as $team) {
            for ($i = 1; $i <= 11; $i++) {
                Player::create([
                    'team_id' => $team->id,
                    'name' => 'Player ' . $i . ' - ' . $team->name,
                    'height' => rand(160, 190),
                    'weight' => rand(60, 90),
                    'position' => $this->getRandomPosition(),
                    'jersey_number' => $i
                ]);
            }
        }
    }

    private function getRandomPosition(): string
    {
        $positions = ['penyerang', 'gelandang', 'bertahan', 'penjaga_gawang'];
        return $positions[array_rand($positions)];
    }
}
