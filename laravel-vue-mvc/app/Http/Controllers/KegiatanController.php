<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Kegiatan::orderByDesc('tanggal_kegiatan')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $kegiatan = Kegiatan::create($data);

        return response()->json($kegiatan, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Kegiatan $kegiatan)
    {
        return $kegiatan;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $data = $this->validateData($request);

        $kegiatan->update($data);

        return $kegiatan;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();

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
            'nama_kegiatan' => ['required', 'string', 'max:255'],
            'tempat_kegiatan' => ['required', 'string', 'max:255'],
            'tanggal_kegiatan' => ['required', 'date'],
            'uraian_kegiatan' => ['required', 'string'],
            'realisasi_pelaksanaan' => ['required', 'string', 'in:terlaksana,tidak'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', 'in:pelaksanaan,laporan'],
            'nama_penyusun' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
