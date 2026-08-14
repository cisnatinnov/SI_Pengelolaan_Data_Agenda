<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Pengingat;
use App\Models\Surat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Surat::orderByDesc('created_at')->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $surat = DB::transaction(function () use ($data) {
            $surat = Surat::create($data);

            $this->insertDisposisi($surat, $data);
            $this->notifyAsistenDaerah($surat);

            return $surat;
        });

        return response()->json($surat, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Surat $surat)
    {
        return $surat;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Surat $surat)
    {
        $data = $this->validateData($request);

        $surat->update($data);

        return $surat;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Surat $surat)
    {
        $surat->delete();

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
     * Auto-create a disposisi record from the surat data.
     * Tandatangan fields are intentionally left blank.
     *
     * @param  array<string, mixed>  $data
     */
    private function insertDisposisi(Surat $surat, array $data): void
    {
        Disposisi::create([
            'surat_id' => $surat->id,
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

    /**
     * Notify every asisten daerah user with a pengingat when a surat is received.
     */
    private function notifyAsistenDaerah(Surat $surat): void
    {
        $asistenUsers = User::whereHas('role', function ($query) {
            $query->where('slug', 'asisten_daerah');
        })->get();

        foreach ($asistenUsers as $user) {
            Pengingat::create([
                'user_id' => $user->id,
                'judul' => "Surat masuk diterima: {$surat->nomor_surat}",
                'deskripsi' => "Surat \"{$surat->perihal}\" dari {$surat->asal_surat} telah diterima dan memerlukan tindak lanjut Anda.",
                'tanggal_pengingat' => $surat->tanggal_pelaksanaan,
                'prioritas' => 'sedang',
                'status' => 'pending',
            ]);
        }
    }
}
