<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
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

        // Asisten Daerah can only sahkan (approve) or tolak (reject) the letter.
        if ($user->role_slug === 'asisten_daerah') {
            $data = $request->validate([
                'keterangan' => ['required', 'string', 'in:diserahkan,ditolak'],
                'alasan' => ['nullable', 'string', 'max:1000', 'required_if:keterangan,ditolak'],
            ]);

            $disposisi->update([
                'keterangan' => $data['keterangan'],
                'alasan' => $data['keterangan'] === 'ditolak' ? $data['alasan'] : null,
            ]);

            return $disposisi;
        }

        if ($user->role_slug !== 'staff') {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $data = $this->validateData($request);

        // Alasan only applies to rejected letters.
        if ($data['keterangan'] !== 'ditolak') {
            $data['alasan'] = null;
        }

        $disposisi->update($data);

        return $disposisi;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Disposisi $disposisi)
    {
        if ($request->user()->role_slug !== 'staff') {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $disposisi->delete();

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
            'tanggal' => ['required', 'date'],
            'nomor_surat' => ['required', 'string', 'max:255'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string', 'max:255'],
            'kepada' => ['required', 'string', 'max:255'],
            'pembawa_surat' => ['required', 'string', 'max:255'],
            'tandatangan_penerima' => ['nullable', 'string', 'max:255'],
            'tandatangan_dituju' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['required', 'string', 'in:diterima,ditolak,diserahkan'],
            'alasan' => ['nullable', 'string', 'max:1000', 'required_if:keterangan,ditolak'],
        ]);
    }
}
