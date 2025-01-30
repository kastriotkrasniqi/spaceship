<?php

namespace App\Models;

use App\Models\Planet;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory;

    protected $fillable = [
        'planet_id',
        'resource_id',
        'quantity',
        'price',
        'created_at',
        'updated_at',
    ];

    public function planet()
    {
        return $this->belongsTo(Planet::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
