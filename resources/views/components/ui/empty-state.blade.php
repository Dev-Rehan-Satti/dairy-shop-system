@props(['icon' => 'clipboard', 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 py-16 text-center']) }}>
    <div class="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
        <x-ui.icon :name="$icon" class="size-7" />
    </div>
    <h3 class="font-semibold text-base-content">{{ $title }}</h3>
    @if ($description)
        <p class="max-w-sm text-sm text-base-content/60">{{ $description }}</p>
    @endif
    {{ $slot ?? '' }}
</div>
