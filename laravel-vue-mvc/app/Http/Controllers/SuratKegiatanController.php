<?php

namespace App\Http\Controllers;

use App\Models\SuratKegiatan;
use Illuminate\Http\Request;

class SuratKegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SuratKegiatan::with('suratUndangan');

        if ($request->filled('surat_undangan_id')) {
            $query->where('surat_undangan_id', $request->integer('surat_undangan_id'));
        }

        return $query->orderByDesc('created_at')->get();
    }

    /**
     * Display the specified resource.
     */
    public function show(SuratKegiatan $suratKegiatan)
    {
        return $suratKegiatan->load('suratUndangan');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuratKegiatan $suratKegiatan)
    {
        $data = $this->validateData($request);

        // Alasan only applies to rejected letters.
        if ($data['keterangan'] !== 'ditolak') {
            $data['alasan'] = null;
        }

        $suratKegiatan->update($data);

        return $suratKegiatan;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuratKegiatan $suratKegiatan)
    {
        $suratKegiatan->delete();

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
            'keterangan' => ['required', 'string', 'in:diterima,ditolak,disahkan'],
            'alasan' => ['nullable', 'string', 'max:1000', 'required_if:keterangan,ditolak'],
        ]);
    }
}