<?php

namespace App\Http\Controllers;

use App\Models\AccidentLog;

class DashboardController extends Controller
{
    public function index()
    {
        $logs = AccidentLog::latest()->paginate(10);
        $latestAccident = AccidentLog::where('is_collision', true)->latest()->first();

        return view('dashboard', compact('logs', 'latestAccident'));
    }

    public function latestStatus()
{
    $latest = AccidentLog::latest()->first();

    return response()->json([
        'id'           => $latest ? $latest->id : null,
        'unit_id'      => $latest ? $latest->unit_id : 'B-01',
        'name'         => $latest ? $latest->name : 'Standby User',
        'vehicle'      => $latest ? $latest->vehicle : 'RC Crash Detector',
        'is_collision' => $latest ? $latest->is_collision : false,
        'confidence'   => $latest ? $latest->confidence : 0,
        'lat'          => $latest ? $latest->lat : -6.2088,
        'lng'          => $latest ? $latest->lng : 106.8456,
        'gyro_x'       => $latest && isset($latest->samples[0]) ? $latest->samples[0][3] : 0,
        'gyro_y'       => $latest && isset($latest->samples[0]) ? $latest->samples[0][4] : 0,
        'gyro_z'       => $latest && isset($latest->samples[0]) ? $latest->samples[0][5] : 0,
        'created_at'   => $latest ? $latest->created_at->format('H:i:s') : null,
    ]);
}
}
