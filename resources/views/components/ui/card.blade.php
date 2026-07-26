@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'card bg-base-100 border border-base-200 shadow-sm']) }}>
    @if ($title || isset($actions))
        <div class="flex items-center justify-between gap-4 border-b border-base-200 px-6 py-4">
            <div>
                @if ($title)
                    <h2 class="font-semibold text-base-content">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="text-sm text-base-content/60">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</div>
