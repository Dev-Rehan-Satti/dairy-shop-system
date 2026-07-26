@props(['color' => 'neutral'])

<span @class([
    'badge badge-sm font-medium',
    'badge-primary' => $color === 'primary',
    'badge-secondary' => $color === 'secondary',
    'badge-success' => $color === 'success',
    'badge-warning' => $color === 'warning',
    'badge-error' => $color === 'error',
    'badge-neutral' => $color === 'neutral',
    'badge-outline' => $color === 'outline',
])>
    {{ $slot }}
</span>
