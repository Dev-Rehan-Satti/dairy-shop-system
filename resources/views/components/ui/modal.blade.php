@props(['show' => false, 'title' => null, 'onClose' => null, 'maxWidth' => 'lg'])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-lg',
    };
@endphp

@if ($show)
    <div class="modal modal-open">
        <div {{ $attributes->merge(['class' => "modal-box $maxWidthClass"]) }}>
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 class="text-lg font-semibold text-base-content">{{ $title }}</h3>
                @if ($onClose)
                    <button type="button" wire:click="{{ $onClose }}" class="btn btn-sm btn-circle btn-ghost">
                        <x-ui.icon name="x" class="size-4" />
                    </button>
                @endif
            </div>

            {{ $slot }}
        </div>
        @if ($onClose)
            <div class="modal-backdrop" wire:click="{{ $onClose }}"></div>
        @endif
    </div>
@endif
