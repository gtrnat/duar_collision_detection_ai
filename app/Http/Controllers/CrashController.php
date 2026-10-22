<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accident;

class CrashController extends Controller
{
public function receiveInference(Request $request)
{
    try {
        $baseLat = -6.2030;
        $baseLng = 106.8400;

        $randomLat = $baseLat + (mt_rand(-50, 50) / 10000);
        $randomLng = $baseLng + (mt_rand(-50, 50) / 10000);

        // Simpan seluruh payload JSON asli dari Arduino
        $rawPayload = $request->all();

        $accident = Accident::create([
            'unit_id'     => $request->input('unitId', $request->input('unit_id', 'B-01')),
            'name'        => $request->input('name', 'Ahmad Fauzi'),
            'vehicle'     => $request->input('vehicle', 'RC Crash Detector Prototype'),
            'g_force'     => floatval($request->input('max_g_force', $request->input('g_force', rand(30, 80) / 10))),
            'pitch'       => floatval($request->input('pitch', 0)), // Diperbarui: Menangkap nilai Pitch (Dongak/Nungging)
            'roll'        => floatval($request->input('roll', 0)),  // Diperbarui: Menangkap nilai Roll (Kanan/Kiri)
            'speed'       => floatval($request->input('max_speed', $request->input('speed', rand(20, 60)))),
            'lat'         => round($randomLat, 6),
            'lng'         => round($randomLng, 6),
            'status'      => 'pending',
            'raw_payload' => $rawPayload, // Otomatis tersimpan sebagai JSON
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data kecelakaan berhasil diterima dan disimpan!',
            'data'    => $accident
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal menyimpan data: ' . $e->getMessage()
        ], 500);
    }
}

    public function getPendingAccidents()
    {
        $pending = Accident::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'count' => $pending->count(),
            'data'  => $pending
        ]);
    }

    public function resolveAccident($id)
    {
        $accident = Accident::find($id);
        if ($accident) {
            $accident->update(['status' => 'resolved']);
        }

        $remaining = Accident::where('status', 'pending')->count();
        return response()->json([
            'status' => 'success',
            'remaining_pending' => $remaining
        ]);
    }
}
