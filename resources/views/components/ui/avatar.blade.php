@props(['name' => '', 'size' => 'size-9'])

@php
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->join('');
@endphp

<div class="avatar avatar-placeholder">
    <div {{ $attributes->merge(['class' => "$size rounded-full bg-primary text-primary-content"]) }}>
        <span class="text-sm font-semibold">{{ mb_strtoupper($initials) ?: '?' }}</span>
    </div>
</div>
