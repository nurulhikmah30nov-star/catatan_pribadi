@props([
    'variant' => 'mint', // mint, peach, sky
    'title' => '',
    'value' => '0',
    'caption' => '',
])

@php
    $styles = [
        'mint' => [
            'card' => 'bg-emerald-50/70 border-emerald-200 text-emerald-950',
            'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'text' => 'text-emerald-800',
        ],
        'peach' => [
            'card' => 'bg-orange-50/70 border-orange-200 text-orange-950',
            'badge' => 'bg-orange-100 text-orange-800 border-orange-200',
            'text' => 'text-orange-800',
        ],
        'sky' => [
            'card' => 'bg-blue-50/70 border-blue-200 text-blue-950',
            'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
            'text' => 'text-blue-800',
        ],
    ][$variant] ?? [
        'card' => 'bg-gray-50 border-gray-200 text-gray-900',
        'badge' => 'bg-gray-100 text-gray-800 border-gray-200',
        'text' => 'text-gray-800',
    ];
@endphp

<div class="p-5 rounded-xl border {{ $styles['card'] }} shadow-sm transition hover:shadow-md flex items-center justify-between">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-600 mb-1">{{ $title }}</p>
        <p class="text-2xl font-bold {{ $styles['text'] }}">{{ $value }}</p>
        @if ($caption)
            <p class="text-xs text-gray-500 mt-1">{{ $caption }}</p>
        @endif
    </div>
    @if (isset($icon))
        <div class="p-3 rounded-lg border {{ $styles['badge'] }}">
            {{ $icon }}
        </div>
    @endif
</div>
