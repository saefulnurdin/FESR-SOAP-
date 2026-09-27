<?php

namespace App\Http\Controllers;

use App\Models\Esp32Device;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Perangkat perekam yang dapat mengirim rekaman ke aplikasi.
 *
 * Token dibuat di sini dan hanya ditampilkan sekali. Setelah itu aplikasi
 * menyimpan hash-nya saja, sehingga daftar perangkat tidak cukup untuk
 * mengunggah.
 */
class Esp32DeviceController extends Controller
{
    /**
     * Show all registered devices.
     */
    public function index(): View
    {
        return view('admin.devices.index', [
            'devices' => Esp32Device::query()
                ->with('user')
                ->latest()
                ->get(),
            'owners' => User::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Register a device and hand out its token once.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'name.required' => 'Nama perangkat wajib diisi.',
            'user_id.required' => 'Pemilik perangkat wajib dipilih.',
            'user_id.exists' => 'Pemilik perangkat tidak valid.',
        ], [
            'name' => 'nama perangkat',
            'user_id' => 'pemilik perangkat',
        ]);

        $token = Str::random(64);

        $device = Esp32Device::create([
            ...$validated,

            /*
             * Hanya hash token yang disimpan. Nilai aslinya diteruskan ke
             * sesi sekali ini supaya bisa disalin ke perangkat, lalu hilang
             * begitu meninggalkan halaman.
             */
            'token_hash' => hash('sha256', $token),
        ]);

        return redirect()
            ->route('admin.devices.index')
            ->with('success', "Perangkat {$device->name} terdaftar.")
            ->with('device_token', $token);
    }

    /**
     * Turn a device on or off without losing its token.
     */
    public function toggle(Esp32Device $esp32Device): RedirectResponse
    {
        $esp32Device->update(['is_active' => ! $esp32Device->is_active]);

        return back()->with('success', $esp32Device->is_active
            ? "Perangkat {$esp32Device->name} diaktifkan."
            : "Perangkat {$esp32Device->name} dinonaktifkan.");
    }

    /**
     * Issue a replacement token, which immediately invalidates the old one.
     */
    public function regenerateToken(Esp32Device $esp32Device): RedirectResponse
    {
        $token = Str::random(64);

        $esp32Device->update(['token_hash' => hash('sha256', $token)]);

        return back()
            ->with('success', "Token baru untuk {$esp32Device->name} sudah diterbitkan. Token lama tidak berlaku lagi.")
            ->with('device_token', $token);
    }

    /**
     * Remove a device from the list.
     */
    public function destroy(Esp32Device $esp32Device): RedirectResponse
    {
        $esp32Device->delete();

        return back()->with('success', "Perangkat {$esp32Device->name} dihapus.");
    }
}
