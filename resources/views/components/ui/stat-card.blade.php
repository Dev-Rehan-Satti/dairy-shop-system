@props(['icon' => 'grid', 'value', 'label', 'color' => 'primary'])

<div {{ $attributes->merge(['class' => 'card bg-base-100 border border-base-200 shadow-sm']) }}>
    <div class="card-body flex-row items-center gap-3 p-4">
        <div @class([
            'flex size-10 shrink-0 items-center justify-center rounded-xl',
            'bg-primary/10 text-primary' => $color === 'primary',
            'bg-secondary/10 text-secondary' => $color === 'secondary',
            'bg-success/10 text-success' => $color === 'success',
            'bg-warning/10 text-warning' => $color === 'warning',
            'bg-error/10 text-error' => $color === 'error',
        ])>
            <x-ui.icon :name="$icon" class="size-5" />
        </div>
        <div class="min-w-0">
            <p class="text-xl leading-tight font-bold text-base-content">{{ $value }}</p>
            <p class="text-sm leading-snug text-base-content/60">{{ $label }}</p>
        </div>
    </div>
</div>
