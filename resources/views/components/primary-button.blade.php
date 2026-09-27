<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-lg bg-teal-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-700 focus:ring-2 focus:ring-teal-500 focus:outline-hidden disabled:opacity-50']) }}>
    {{ $slot }}
</button>
