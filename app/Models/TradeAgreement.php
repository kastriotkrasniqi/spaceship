<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TradeAgreement extends Model
{
    /** @use HasFactory<\Database\Factories\TradeAgreementFactory> */
    use HasFactory;


    protected $fillable = [
        'origin_id',
        'destination_id',
        'resource_id',
        'quantity',
        'frequency',
        'next_delivery',
        'created_at',
        'updated_at',
    ];


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
        return $this->belongsTo(Resource::class, 'resource_id');
    }
}
