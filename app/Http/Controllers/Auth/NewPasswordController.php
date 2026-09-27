<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $this->messageFor($status)]);
        }

        return redirect()
            ->route('login')
            ->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk dengan kata sandi baru.');
    }

    /**
     * Terjemahkan status bawaan Laravel ke bahasa Indonesia.
     */
    private function messageFor(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'Tautan pengaturan ulang kata sandi tidak valid atau sudah kedaluwarsa.',
            Password::INVALID_USER => 'Email tersebut tidak terdaftar.',
            Password::RESET_THROTTLED => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
            default => 'Permintaan tidak dapat diproses. Silakan coba lagi.',
        };
    }
}
