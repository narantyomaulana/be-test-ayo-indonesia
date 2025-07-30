<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Matches extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'match_date',
        'match_time',
        'home_team_id',
        'away_team_id',
        'home_score',
        'away_score',
        'status'
    ];

    protected $casts = [
        'match_date' => 'date',
        'match_time' => 'datetime:H:i'
    ];

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }

    public function getWinnerAttribute()
    {
        if ($this->status !== 'completed') return null;

        if ($this->home_score > $this->away_score) return 'home';
        if ($this->away_score > $this->home_score) return 'away';
        return 'draw';
    }
}
