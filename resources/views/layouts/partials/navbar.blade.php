@php
    $user = auth()->user();
@endphp

<nav class="border-b border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight">
                <span class="inline-flex size-8 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white">F</span>
                <span>{{ config('app.name') }}</span>
            </a>

            @auth
                <div class="hidden items-center gap-1 text-sm md:flex">
                    <a href="{{ route('dashboard') }}"
                        @class([
                            'rounded-lg px-3 py-1.5 font-medium transition',
                            'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white' => request()->routeIs('dashboard'),
                            'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('dashboard'),
                        ])>
                        Dasbor
                    </a>

                    <a href="{{ route('patients.index') }}"
                        @class([
                            'rounded-lg px-3 py-1.5 font-medium transition',
                            'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white' => request()->routeIs('patients.*'),
                            'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('patients.*'),
                        ])>
                        Pasien
                    </a>

                    @can('manage-document-templates')
                        <a href="{{ route('admin.document-types.index') }}"
                            @class([
                                'rounded-lg px-3 py-1.5 font-medium transition',
                                'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white' => request()->routeIs('admin.document-types.*', 'admin.templates.*'),
                                'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('admin.document-types.*', 'admin.templates.*'),
                            ])>
                            Template dokumen
                        </a>
                    @endcan

                    @can('manage-recording-devices')
                        <a href="{{ route('admin.devices.index') }}"
                            @class([
                                'rounded-lg px-3 py-1.5 font-medium transition',
                                'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white' => request()->routeIs('admin.devices.*'),
                                'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('admin.devices.*'),
                            ])>
                            Perangkat perekam
                        </a>
                    @endcan

                    @can('manage-users')
                        <a href="{{ route('admin.users.index') }}"
                            @class([
                                'rounded-lg px-3 py-1.5 font-medium transition',
                                'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-white' => request()->routeIs('admin.users.*'),
                                'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' => ! request()->routeIs('admin.users.*'),
                            ])>
                            Pengguna
                        </a>
                    @endcan
                </div>
            @endauth
        </div>

        <div class="flex items-center gap-3 text-sm">
            @auth
                <div x-data="{ open: false }" class="relative">
                    <button type="button" x-on:click="open = ! open" x-on:keydown.escape="open = false"
                        class="flex items-center gap-2 rounded-lg px-3 py-1.5 font-medium text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                        <span class="hidden max-w-40 truncate sm:inline">{{ $user->name }}</span>
                        <span class="hidden text-xs text-slate-400 sm:inline dark:text-slate-500">{{ $user->is_admin ? 'Administrator' : 'Petugas' }}</span>
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" x-on:click.outside="open = false" x-cloak
                        class="absolute right-0 z-50 mt-2 w-56 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                            <p class="truncate text-sm font-medium">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                        </div>

                        <div class="p-1">
                            <a href="{{ route('profile.edit') }}"
                                class="block rounded-md px-4 py-2 text-sm text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                Profil saya
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full rounded-md px-4 py-2 text-left text-sm text-slate-600 transition hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}"
                        class="rounded-lg bg-teal-600 px-3 py-1.5 font-medium text-white hover:bg-teal-700">
                        Masuk
                    </a>
                @endif
            @endauth
        </div>
    </div>
</nav>
