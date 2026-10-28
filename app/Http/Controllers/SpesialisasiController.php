<?php

namespace App\Http\Controllers;

use App\Models\Spesialisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpesialisasiController extends Controller
{
    public function index(): View
    {
        $spesialisasis = Spesialisasi::withCount('dokters')->orderBy('nama_spesialisasi')->paginate(15);
        return view('spesialisasi.index', compact('spesialisasis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_spesialisasi' => ['required', 'string', 'max:150', 'unique:spesialisasi,nama_spesialisasi'],
        ], [
            'nama_spesialisasi.unique' => 'Nama spesialisasi tersebut sudah terdaftar.',
        ]);

        Spesialisasi::create($validated);
        return back()->with('success', "Spesialisasi {$validated['nama_spesialisasi']} berhasil ditambahkan.");
    }

    public function update(Request $request, Spesialisasi $spesialisasi): RedirectResponse
    {
        $validated = $request->validate([
            'nama_spesialisasi' => ['required', 'string', 'max:150', 'unique:spesialisasi,nama_spesialisasi,' . $spesialisasi->id],
        ]);

        $spesialisasi->update($validated);
        return back()->with('success', "Data spesialisasi berhasil diperbarui.");
    }

    public function destroy(Spesialisasi $spesialisasi): RedirectResponse
    {
        if ($spesialisasi->dokters()->count() > 0) {
            return back()->with('error', "Spesialisasi ini tidak dapat dihapus karena masih memiliki {$spesialisasi->dokters()->count()} dokter aktif.");
        }

        $spesialisasi->delete();
        return back()->with('success', "Spesialisasi berhasil dihapus.");
    }
}
