@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, warning
    'class' => '',
])

@php
    $base = 'inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm';
    $variants = [
        'primary' => 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500 border border-transparent',
        'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 focus:ring-emerald-500',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 border border-transparent',
        'warning' => 'bg-orange-500 hover:bg-orange-600 text-white focus:ring-orange-400 border border-transparent',
    ];
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . $class;
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
