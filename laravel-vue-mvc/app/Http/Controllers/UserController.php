<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return User::orderBy('name')->get(['id', 'name', 'email', 'role', 'created_at']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'in:admin,staff,asisten_daerah,opd'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user->only('id', 'name', 'email', 'role', 'created_at'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user->only('id', 'name', 'email', 'role', 'created_at');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', 'in:admin,staff,asisten_daerah,opd'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user->only('id', 'name', 'email', 'role', 'created_at');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun sendiri.'], 422);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
