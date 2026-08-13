<?php

namespace App\Http\Controllers;

use App\Models\KendaliSuratMasuk;
use App\Models\SuratKegiatan;
use App\Models\SuratUndangan;
use Illuminate\Http\Request;

class SuratUndanganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return SuratUndangan::orderByDesc('created_at')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $suratUndangan = SuratUndangan::create($data);

        $this->insertKendali($data);
        $this->insertSuratKegiatan($suratUndangan, $data);

        return response()->json($suratUndangan, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(SuratUndangan $suratUndangan)
    {
        return $suratUndangan;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuratUndangan $suratUndangan)
    {
        $data = $this->validateData($request);

        $suratUndangan->update($data);

        return $suratUndangan;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuratUndangan $suratUndangan)
    {
        $suratUndangan->delete();

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
            'tanggal_pelaksanaan' => ['required', 'date'],
            'tempat_pelaksanaan' => ['required', 'string', 'max:255'],
            'pembawa_surat' => ['required', 'string', 'max:255'],
            'tandatangan' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * Auto-insert a kendali surat masuk record.
     *
     * @param  array<string, mixed>  $data
     */
    private function insertKendali(array $data): void
    {
        KendaliSuratMasuk::create([
            'tanggal_surat' => $data['tanggal'],
            'perihal' => $data['perihal'],
            'keterangan' => 'diterima',
        ]);
    }

    /**
     * Auto-create a surat kegiatan record from the surat undangan data.
     * Tandatangan fields are intentionally left blank.
     *
     * @param  array<string, mixed>  $data
     */
    private function insertSuratKegiatan(SuratUndangan $suratUndangan, array $data): void
    {
        SuratKegiatan::create([
            'surat_undangan_id' => $suratUndangan->id,
            'tanggal' => $data['tanggal'],
            'nomor_surat' => $data['nomor_surat'],
            'asal_surat' => $data['asal_surat'],
            'perihal' => $data['perihal'],
            'kepada' => $data['kepada'],
            'pembawa_surat' => $data['pembawa_surat'],
            'tandatangan_penerima' => null,
            'tandatangan_dituju' => null,
            'keterangan' => 'diterima',
        ]);
    }
}