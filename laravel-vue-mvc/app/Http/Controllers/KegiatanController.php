<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\KegiatanKehadiran;
use App\Models\Pengingat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Kegiatan::withCount([
            'kehadiran as hadir_count' => fn ($query) => $query->where('status', 'hadir'),
        ])->withCount([
            'kehadiran as tidak_count' => fn ($query) => $query->where('status', 'tidak'),
        ])->with('kehadiran.user')
          ->orderByDesc('tanggal_kegiatan')
          ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $kegiatan = DB::transaction(function () use ($data) {
            $kegiatan = Kegiatan::create($data);

            $this->notifyAllRoles($kegiatan);

            return $kegiatan;
        });

        return response()->json($kegiatan, 201);
    }

    /**
     * Confirm attendance (hadir/tidak) for a kegiatan. Only OPD users.
     */
    public function konfirmasiKehadiran(Request $request, Kegiatan $kegiatan)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:hadir,tidak'],
        ]);

        $kehadiran = KegiatanKehadiran::updateOrCreate(
            [
                'kegiatan_id' => $kegiatan->id,
                'user_id' => $request->user()->id,
            ],
            ['status' => $data['status']]
        );

        return response()->json($kehadiran, 200);
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

    /**
     * Notify every user (all roles) with a pengingat when a kegiatan is added.
     */
    private function notifyAllRoles(Kegiatan $kegiatan): void
    {
        foreach (User::all() as $user) {
            Pengingat::create([
                'user_id' => $user->id,
                'judul' => "Kegiatan baru: {$kegiatan->nama_kegiatan}",
                'deskripsi' => "Kegiatan \"{$kegiatan->nama_kegiatan}\" akan dilaksanakan pada {$kegiatan->tanggal_kegiatan} di {$kegiatan->tempat_kegiatan}.",
                'tanggal_pengingat' => $kegiatan->tanggal_kegiatan,
                'prioritas' => 'sedang',
                'status' => 'pending',
            ]);
        }
    }
}
