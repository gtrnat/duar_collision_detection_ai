<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccidentLog extends Model
{
    protected $fillable = [
        'unit_id',
        'name',
        'vehicle',
        'is_collision',
        'confidence',
        'sample_rate_hz',
        'window_sec',
        'lat',
        'lng',
        'samples',
    ];

    protected $casts = [
        'is_collision' => 'boolean',
        'samples'      => 'array',
    ];
}
