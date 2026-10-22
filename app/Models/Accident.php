<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accident extends Model
{
    use HasFactory;

    // Pastikan 'pitch' dan 'roll' wajib ada di dalam array ini!
    protected $fillable = [
        'unit_id',
        'name',
        'vehicle',
        'g_force',
        'pitch',      // <--- IZINKAN KOLOM PITCH
        'roll',       // <--- IZINKAN KOLOM ROLL
        'speed',
        'lat',
        'lng',
        'status',
        'raw_payload'
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];
}
