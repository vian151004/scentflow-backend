<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $attendance = Attendance::with(['user', 'cashClosing'])
            ->where('user_id', $request->user()->id)
            ->orderBy('date', 'desc')
            ->paginate(10);

        return AttendanceResource::collection($attendance);
    }

    /**
     * Display today's attendance for the authenticated user.
     */
    public function today(Request $request)
    {
        $today = Carbon::now()->toDateString();
        $attendance = Attendance::with(['user', 'cashClosing'])
            ->where('user_id', $request->user()->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'message' => 'Belum melakukan absensi hari ini.',
                'data' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data absensi hari ini ditemukan.',
            'data' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $today = Carbon::now()->toDateString();

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini.',
                'data' => new AttendanceResource($existingAttendance),
            ], 400);
        }

        $validated = $request->validate([
            'initial_cash' => 'required|numeric|min:0',
            'image_proof' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image_proof')) {
            $imagePath = $request->file('image_proof')->store('attendance_proofs', 'public');
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => Carbon::now()->format('H:i:s'),
            'initial_cash' => $validated['initial_cash'],
            'image_proof' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil, selamat bekerja!',
            'data' => new AttendanceResource($attendance),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attendance = Attendance::with(['user', 'cashClosing'])->find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Data absensi tidak ditemukan.',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Data absensi ditemukan.',
            'data' => new AttendanceResource($attendance),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Data absensi tidak ditemukan',
            ], 404);
        }

        if ($attendance->clock_out !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah melakukan Clock Out hari ini.',
            ], 400);
        }

        $attendance->update([
            'clock_out' => Carbon::now()->toTimeString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clock Out berhasil, terima kasih atas kerja kerasnya!',
            'data'    => new AttendanceResource($attendance)
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Data absensi tidak ditemukan',
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi berhasil dihapus',
        ], 200);
    }
}
