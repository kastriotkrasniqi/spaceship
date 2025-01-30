<?php

namespace App\Models;

use App\Enums\StarshipStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Starship extends Model
{
    /** @use HasFactory<\Database\Factories\StarshipFactory> */
    use HasFactory;


    protected $fillable = [
        'name',
        'cargo_capacity',
        'status',
        'assigned_route_id',
        'created',
        'updated',
    ];


    protected function casts(): array
    {
        return [
            'status' => StarshipStatus::class,
        ];
    }


    public function tradeRoutes(){
        return $this->hasOne(TradeRoute::class);
    }
}
