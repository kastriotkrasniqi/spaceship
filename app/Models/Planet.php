<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planet extends Model
{
    /** @use HasFactory<\Database\Factories\PlanetFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];


    public function inventories(){
        return $this->hasMany(Inventory::class);
    }

    public function tradeRoutes(){
        return $this->belongsToMany(TradeRoute::class);
    }

}
