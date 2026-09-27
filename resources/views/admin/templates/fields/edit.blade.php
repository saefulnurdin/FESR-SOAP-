@extends('layouts.app')

@section('title', 'Ubah Isian — ' . $template->name . ' — ' . config('app.name'))

@section('content')
    <div class="mx-auto max-w-2xl">
        <nav class="text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('admin.document-types.index') }}" class="hover:underline">Jenis dokumen</a>
            <span class="mx-1">/</span>
            <a href="{{ route('admin.templates.edit', $template) }}" class="hover:underline">{{ $template->name }}</a>
        </nav>

        <h1 class="mt-4 text-2xl font-bold tracking-tight">Ubah isian</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Isian {{ $field->label }} pada bagian {{ $section->title }}.
        </p>

        <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            @include('admin.templates.partials.field-form')
        </div>
    </div>
@endsection
