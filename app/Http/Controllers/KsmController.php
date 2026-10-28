<?php

namespace App\Http\Controllers;

use App\Models\Ksm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KsmController extends Controller
{
    public function index(): View
    {
        $ksms = Ksm::withCount('dokters')->orderBy('nama_ksm')->paginate(15);
        return view('ksm.index', compact('ksms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_ksm' => ['required', 'string', 'max:150', 'unique:ksm,nama_ksm'],
        ], [
            'nama_ksm.unique' => 'Nama KSM tersebut sudah terdaftar.',
        ]);

        Ksm::create($validated);
        return back()->with('success', "KSM {$validated['nama_ksm']} berhasil ditambahkan.");
    }

    public function update(Request $request, Ksm $ksm): RedirectResponse
    {
        $validated = $request->validate([
            'nama_ksm' => ['required', 'string', 'max:150', 'unique:ksm,nama_ksm,' . $ksm->id],
        ]);

        $ksm->update($validated);
        return back()->with('success', "Data KSM berhasil diperbarui.");
    }

    public function destroy(Ksm $ksm): RedirectResponse
    {
        if ($ksm->dokters()->count() > 0) {
            return back()->with('error', "KSM ini tidak dapat dihapus karena masih memiliki {$ksm->dokters()->count()} dokter aktif.");
        }

        $ksm->delete();
        return back()->with('success', "KSM berhasil dihapus.");
    }
}
