<?php

namespace App\Http\Middleware;

use App\Models\Esp32Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mengenali perangkat perekam dari token yang dikirimnya.
 *
 * Token dikirim sebagai header X-Device-Token karena perangkat tidak punya
 * peramban dan tidak bisa menyimpan sesi. Yang dibandingkan adalah hash-nya,
 * sehingga isi tabel saja tidak cukup untuk mengunggah.
 *
 * Permintaan diteruskan sebagai permintaan pemilik perangkat, sehingga
 * controller yang sama bisa dipakai oleh peramban maupun perangkat tanpa
 * percabangan. Perangkatnya sendiri tersedia lewat $request->attributes.
 */
class AuthenticateDevice
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Device-Token');

        abort_if(blank($token), 401, 'Token perangkat wajib disertakan.');

        $device = Esp32Device::query()
            ->with('user')
            ->where('token_hash', hash('sha256', $token))
            ->first();

        abort_if($device === null, 401, 'Token perangkat tidak dikenali.');
        abort_unless($device->is_active, 403, 'Perangkat ini sudah dinonaktifkan.');
        abort_unless($device->user->is_active, 403, 'Akun pemilik perangkat sedang tidak aktif.');

        $device->forceFill(['last_seen_at' => now()])->saveQuietly();

        $request->attributes->set('device', $device);
        $request->setUserResolver(fn (): mixed => $device->user);

        return $next($request);
    }
}
