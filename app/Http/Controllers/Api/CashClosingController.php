<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CashClosingResource;
use App\Models\Attendance;
use App\Models\CashClosing;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CashClosingController extends Controller
{
    /**
     * Menampilkan daftar riwayat tutup kasir user yang login.
     */
    public function index(Request $request)
    {
        $closings = CashClosing::whereHas('attendance', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->latest()->paginate();

        return CashClosingResource::collection($closings);
    }

    /**
     * Menyimpan data tutup kasir baru.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $today = Carbon::now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum melakukan absensi hari ini!',
            ], 400);
        }

        if ($attendance->cashClosing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan tutup kasir untuk shift hari ini!',
                'data'    => new CashClosingResource($attendance->cashClosing),
            ], 400);
        }

        $validated = $request->validate([
            'physical_cash_total' => 'required|numeric|min:0',
            'notes'               => 'nullable|string|max:500',
        ]);

        $systemCashTotal   = (float) $attendance->initial_cash;
        $physicalCashTotal = (float) $validated['physical_cash_total'];
        $discrepancyAmount = $physicalCashTotal - $systemCashTotal;

        $notes = $validated['notes'] ?? null;

        if (!$notes) {
            if ($discrepancyAmount == 0) {
                $notes = 'Kas sesuai, tidak ada selisih.';
            } elseif ($discrepancyAmount > 0) {
                $notes = 'Terdapat kelebihan kas sebesar Rp ' . number_format($discrepancyAmount, 0, ',', '.');
            } else {
                $notes = 'Terdapat kekurangan kas sebesar Rp ' . number_format(abs($discrepancyAmount), 0, ',', '.');
            }
        }

        $cashClosing = CashClosing::create([
            'user_id'             => $user->id,
            'attendance_id'       => $attendance->id,
            'system_cash_total'   => $systemCashTotal,
            'physical_cash_total' => $physicalCashTotal,
            'discrepancy_amount'  => $discrepancyAmount,
            'notes'               => $notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tutup Kasir berhasil dicatat. Jangan lupa melakukan Clock Out!',
            'data'    => new CashClosingResource($cashClosing),
        ], 201);
    }

    /**
     * Menampilkan detail data tutup kasir berdasarkan ID.
     */
    public function show(string $id)
    {
        $cashClosing = CashClosing::find($id);

        if (!$cashClosing) {
            return response()->json([
                'success' => false,
                'message' => 'Data Tutup Kasir tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Tutup Kasir ditemukan.',
            'data'    => new CashClosingResource($cashClosing),
        ]);
    }
}