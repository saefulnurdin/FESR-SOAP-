<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex h-full min-h-screen flex-col bg-slate-50 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-lg font-semibold tracking-tight">
            <span class="inline-flex size-8 items-center justify-center rounded-lg bg-teal-600 text-sm font-bold text-white">F</span>
            <span>{{ config('app.name') }}</span>
        </a>

        <div class="mt-6 w-full max-w-md">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                @include('layouts.partials.flash')

                <h1 class="text-xl font-semibold tracking-tight">@yield('heading')</h1>

                @hasSection('description')
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">@yield('description')</p>
                @endif

                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>
