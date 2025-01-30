<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeRoute extends Model
{
    protected $fillable = [
        'name',
        'origin_id',
        'destination_id',
        'resource_id',
        'quantity',
        'travel_time',
        'created_at',
        'updated_at'
    ];

    public function starship()
    {
        return $this->hasOne(Starship::class, 'assigned_route_id');
    }

    public function origin()
    {
        return $this->belongsTo(Planet::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Planet::class, 'destination_id');
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
