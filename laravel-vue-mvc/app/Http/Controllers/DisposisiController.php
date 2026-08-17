<?php

namespace App\Http\Controllers;

use App\Events\PengingatNotification;
use App\Models\Disposisi;
use App\Models\Pengingat;
use App\Models\User;
use Illuminate\Http\Request;

class DisposisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Disposisi::with('surat');

        if ($request->filled('surat_id')) {
            $query->where('surat_id', $request->integer('surat_id'));
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(Disposisi $disposisi)
    {
        return $disposisi->load('surat');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Disposisi $disposisi)
    {
        $user = $request->user();

        // Only Asisten Daerah can sahkan (approve) or tolak (reject) the letter.
        if ($user->role_slug !== 'asisten_daerah') {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $data = $request->validate([
            'keterangan' => ['required', 'string', 'in:diserahkan,ditolak'],
            'tandatangan_penerima' => ['nullable', 'string', 'max:255', 'required_if:keterangan,diserahkan'],
            'tandatangan_dituju' => ['nullable', 'string', 'max:255', 'required_if:keterangan,diserahkan'],
            'alasan' => ['nullable', 'string', 'max:1000', 'required_if:keterangan,ditolak'],
        ]);

        $disposisi->update([
            'keterangan' => $data['keterangan'],
            'tandatangan_penerima' => $data['keterangan'] === 'diserahkan' ? $data['tandatangan_penerima'] : null,
            'tandatangan_dituju' => $data['keterangan'] === 'diserahkan' ? $data['tandatangan_dituju'] : null,
            'alasan' => $data['keterangan'] === 'ditolak' ? $data['alasan'] : null,
        ]);

        $this->notifyStaff($disposisi, $data['keterangan'], $data['alasan'] ?? null);

        return $disposisi;
    }

    /**
     * Notify every staff user with a pengingat when a disposisi is
     * accepted (diserahkan) or rejected (ditolak) by the Asisten Daerah.
     */
    private function notifyStaff(Disposisi $disposisi, string $keterangan, ?string $alasan = null): void
    {
        $staffUsers = User::whereHas('role', function ($query) {
            $query->where('slug', 'staff');
        })->get();

        foreach ($staffUsers as $user) {
            $pengingat = Pengingat::create([
                'user_id' => $user->id,
                'judul' => $keterangan === 'diserahkan'
                    ? "Disposisi diserahkan: {$disposisi->nomor_surat}"
                    : "Disposisi ditolak: {$disposisi->nomor_surat}",
                'deskripsi' => $keterangan === 'diserahkan'
                    ? "Disposisi surat \"{$disposisi->perihal}\" telah diserahkan oleh Asisten Daerah."
                    : "Disposisi surat \"{$disposisi->perihal}\" ditolak oleh Asisten Daerah" . ($alasan ? " dengan alasan: {$alasan}" : '') . '.',
                'tanggal_pengingat' => now(),
                'prioritas' => 'sedang',
                'status' => 'pending',
                'source' => 'disposisi',
            ]);

            broadcast(new PengingatNotification($pengingat));
        }
    }
}
