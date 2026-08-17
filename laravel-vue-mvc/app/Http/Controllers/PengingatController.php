<?php

namespace App\Http\Controllers;

use App\Models\Pengingat;
use Illuminate\Http\Request;

class PengingatController extends Controller
{
    /**
     * Display a listing of the current user's pengingat.
     */
    public function index(Request $request)
    {
        return $request->user()
            ->pengingats()
            ->orderBy('tanggal_pengingat')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $pengingat = $request->user()->pengingats()->create($data + ['source' => 'manual']);

        return response()->json($pengingat, 201);
    }

    /**
     * Display the current user's notifications.
     * Only pengingat auto-generated from surat/kegiatan are listed.
     */
    public function notifications(Request $request)
    {
        $notifications = $request->user()
            ->pengingats()
            ->notifications()
            ->orderByDesc('created_at')
            ->get();

        $unreadCount = $request->user()
            ->pengingats()
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, Pengingat $pengingat)
    {
        abort_unless($pengingat->user_id === $request->user()->id, 404);

        if ($pengingat->read_at === null) {
            $pengingat->update(['read_at' => now()]);
        }

        return $pengingat;
    }

    /**
     * Mark all of the current user's notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->pengingats()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'Semua notifikasi telah dibaca.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Pengingat $pengingat)
    {
        abort_unless($pengingat->user_id === $request->user()->id, 404);

        return $pengingat;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pengingat $pengingat)
    {
        abort_unless($pengingat->user_id === $request->user()->id, 404);

        $data = $this->validateData($request);

        $pengingat->update($data);

        return $pengingat;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Pengingat $pengingat)
    {
        abort_unless($pengingat->user_id === $request->user()->id, 404);

        $pengingat->delete();

        return response()->json(null, 204);
    }

    /**
     * Validate the request data.
     *
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'tanggal_pengingat' => ['required', 'date'],
            'prioritas' => ['required', 'string', 'in:rendah,sedang,tinggi'],
            'status' => ['required', 'string', 'in:pending,selesai'],
        ]);
    }
}
