@if (session('success'))
    <x-alert type="success">{{ session('success') }}</x-alert>
@endif

@if ($errors->any())
    <x-alert type="error">
        <p class="font-medium">Periksa kembali isian Anda:</p>
        <ul class="mt-1 list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
