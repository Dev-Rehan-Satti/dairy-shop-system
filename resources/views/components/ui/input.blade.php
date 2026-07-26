@props(['label' => null, 'error' => null, 'hint' => null])

@php
    $isPassword = $attributes->get('type') === 'password';
@endphp

<label class="form-control w-full" @if ($isPassword) x-data="{ showPassword: false }" @endif>
    @if ($label)
        <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">{{ $label }}</span>
    @endif

    @if ($isPassword)
        <div class="relative">
            <input
                x-bind:type="showPassword ? 'text' : 'password'"
                {{ $attributes->except('type')->merge(['class' => 'input input-bordered w-full pr-10' . ($error ? ' input-error' : '')]) }}
            />
            <button
                type="button"
                tabindex="-1"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 flex items-center px-3 text-base-content/40 hover:text-base-content/70"
            >
                <x-ui.icon name="eye" class="size-4" x-show="!showPassword" />
                <x-ui.icon name="eye-off" class="size-4" x-show="showPassword" x-cloak />
            </button>
        </div>
    @else
        <input {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($error ? ' input-error' : '')]) }} />
    @endif

    @if ($error)
        <span class="mt-1 text-xs text-error">{{ $error }}</span>
    @elseif ($hint)
        <span class="mt-1 text-xs text-base-content/50">{{ $hint }}</span>
    @endif
</label>
