<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'logo',
        'founded_year',
        'headquarters_address',
        'headquarters_city'
    ];

    public function players()
    {
        return $this->hasMany(Player::class);
    }

    public function homeMatches()
    {
        return $this->hasMany(Matches::class, 'home_team_id');
    }

    public function awayMatches()
    {
        return $this->hasMany(Matches::class, 'away_team_id');
    }

    public function matches()
    {
        return $this->homeMatches()->union($this->awayMatches());
    }
}
