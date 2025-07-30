<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'height',
        'weight',
        'position',
        'jersey_number'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }
}
