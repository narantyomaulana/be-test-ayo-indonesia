<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'Persija Jakarta',
                'logo' => 'https://example.com/persija-logo.png',
                'founded_year' => 1928,
                'headquarters_address' => 'Jl. Pintu Satu Senayan, Jakarta',
                'headquarters_city' => 'Jakarta'
            ],
            [
                'name' => 'Persib Bandung',
                'logo' => 'https://example.com/persib-logo.png',
                'founded_year' => 1933,
                'headquarters_address' => 'Jl. Sulanjana No. 17, Bandung',
                'headquarters_city' => 'Bandung'
            ],
            [
                'name' => 'Arema FC',
                'logo' => 'https://example.com/arema-logo.png',
                'founded_year' => 1987,
                'headquarters_address' => 'Jl. Laksamana Martadinata, Malang',
                'headquarters_city' => 'Malang'
            ]
        ];

        foreach ($teams as $team) {
            Team::create($team);
        }
    }
}
