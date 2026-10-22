<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accident;

class CrashController extends Controller
{
    // 1. Endpoint untuk menerima data POST dari ESP32
    public function receiveInference(Request $request)
    {
        try {
            $rawPayload = $request->all();

            // Ambil Koordinat GPS Asli dari modul NEO-6M (ESP32)
            $realLat = floatval($request->input('latitude', -6.2030));
            $realLng = floatval($request->input('longitude', 106.8400));

            // Cek apakah ada insiden aktif yang masih berstatus 'pending'
            $pendingCount = Accident::where('status', 'pending')->count();

            if ($pendingCount === 0) {
                // --- KECELAKAAN PERTAMA: Gunakan Lokasi GPS Asli ---
                $finalLat   = round($realLat, 6);
                $finalLng   = round($realLng, 6);
                $victimName = $request->input('name', 'Ahmad Fauzi') . " (GPS Asli)";
            } else {
                // --- KECELAKAAN SELANJUTNYA: Otomatis Titik Dummy ---
                $latOffset  = (rand(-50, 50) / 10000);
                $lngOffset  = (rand(-50, 50) / 10000);

                $finalLat   = round($realLat + $latOffset, 6);
                $finalLng   = round($realLng + $lngOffset, 6);

                $dummyNames = ['Budi Santoso', 'Siti Rahma', 'Rizky Pratama', 'Eko Prasetyo'];
                $victimName = $dummyNames[($pendingCount - 1) % count($dummyNames)] . " (Auto-Dummy)";
            }

            // Simpan Ke Database
            $accident = Accident::create([
                'unit_id'     => 'B-0' . ($pendingCount + 1),
                'name'        => $victimName,
                'vehicle'     => $request->input('vehicle', 'RC Crash Detector Prototype'),
                'g_force'     => floatval($request->input('max_g_force', 0)),
                'pitch'       => floatval($request->input('pitch', 0)), // Nilai Dongak/Nungging
                'roll'        => floatval($request->input('roll', 0)),  // Nilai Kanan/Kiri
                'speed'       => floatval($request->input('speed', 0)),
                'lat'         => $finalLat,
                'lng'         => $finalLng,
                'status'      => 'pending',
                'raw_payload' => $rawPayload,
            ]);

            return response()->json([
                'status'        => 'success',
                'is_auto_dummy' => $pendingCount > 0,
                'data'          => $accident
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Endpoint untuk mengambil insiden aktif (polling Dashboard 2 detik)
    public function getPendingAccidents()
    {
        $pending = Accident::where('status', 'pending')
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'count' => $pending->count(),
            'data'  => $pending
        ]);
    }

    // 3. Endpoint untuk mengambil semua riwayat (Dashboard History Sheet)
    public function getAccidents()
    {
        $accidents = Accident::orderBy('created_at', 'desc')->get();
        return response()->json($accidents);
    }

    // 4. Endpoint untuk menyelesaikan insiden (Tombol Evakuasi)
    public function resolveAccident($id)
    {
        $accident = Accident::find($id);
        if ($accident) {
            $accident->update(['status' => 'resolved']);
            return response()->json(['status' => 'success', 'message' => 'Insiden diselesaikan']);
        }
        return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
    }
}
