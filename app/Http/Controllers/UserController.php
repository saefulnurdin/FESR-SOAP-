<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Manajemen pengguna yang hanya dapat diakses administrator.
 */
class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('search'), function (Builder $query) use ($request): void {
                $search = trim((string) $request->string('search'));

                $query->where(fn (Builder $searchQuery) => $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                );
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User(Arr::only($request->validated(), ['name', 'email', 'password']));

        $user->forceFill([
            'is_admin' => $request->boolean('is_admin'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dibuat.");
    }

    /**
     * Show the form for editing the given user.
     */
    public function edit(Request $request, User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'isSelf' => $user->is($request->user()),
        ]);
    }

    /**
     * Update the given user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $attributes = Arr::only($request->validated(), ['name', 'email']);

        if (filled($request->input('password'))) {
            $attributes['password'] = (string) $request->string('password');
        }

        /*
         * Administrator tidak boleh mengubah hak akses akunnya sendiri, baik
         * saat menyunting akun orang lain. Karena hanya administrator aktif
         * yang dapat membuka halaman ini, aturan ini juga menjamin sistem
         * selalu menyisakan minimal satu administrator aktif.
         */
        if (! $user->is($request->user())) {
            $attributes['is_admin'] = $request->boolean('is_admin');
            $attributes['is_active'] = $request->boolean('is_active');
        }

        $user->forceFill($attributes)->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil diperbarui.");
    }

    /**
     * Remove the given user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages([
                'user' => 'Anda tidak dapat menghapus akun Anda sendiri.',
            ]);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Akun {$user->name} berhasil dihapus.");
    }
}
