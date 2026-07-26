@props(['href', 'icon' => 'grid', 'active' => null])

@php
    $isActive = $active ?? request()->url() === url($href);
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors '
            . ($isActive
                ? 'bg-primary text-primary-content'
                : 'text-base-content/70 hover:bg-base-200 hover:text-base-content'),
    ]) }}
>
    <x-ui.icon :name="$icon" class="size-5 shrink-0" />
    <span class="truncate [.collapsed_&]:hidden">{{ $slot }}</span>
</a>
