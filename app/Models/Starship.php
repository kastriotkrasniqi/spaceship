<?php

namespace App\Models;

use App\Enums\StarshipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Starship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cargo_capacity',
        'status',
        'assigned_route_id'
    ];

    public function assignedRoute()
    {
        return $this->belongsTo(TradeRoute::class, 'assigned_route_id');
    }

    protected function casts(): array
    {
        return [
            'status' => StarshipStatus::class,
        ];
    }


    
}
