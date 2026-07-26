@props(['title'])

<div class="space-y-1">
    <p class="px-3 text-xs font-semibold tracking-wider text-base-content/40 uppercase [.collapsed_&]:hidden">{{ $title }}</p>
    <div class="space-y-0.5">
        {{ $slot }}
    </div>
</div>
