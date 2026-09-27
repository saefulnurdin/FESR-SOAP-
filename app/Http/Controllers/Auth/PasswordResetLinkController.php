<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        /*
         * Balasan selalu sama agar halaman ini tidak membocorkan email mana
         * saja yang terdaftar, dan tautan tidak dikirim ke akun yang sudah
         * dinonaktifkan.
         */
        $user = User::query()->where('email', $request->string('email'))->where('is_active', true)->first();

        $status = $user === null
            ? Password::INVALID_USER
            : Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.']);
        }

        return back()->with(
            'success',
            'Jika email tersebut terdaftar, tautan pengaturan ulang kata sandi telah dikirim ke email Anda.',
        );
    }
}
