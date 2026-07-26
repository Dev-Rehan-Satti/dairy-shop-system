@props(['variant' => 'primary', 'outline' => false, 'size' => null])

<button {{ $attributes->merge([
    'type' => 'button',
    'class' => 'btn ' . "btn-{$variant}" . ($outline ? ' btn-outline' : '') . ($size ? " btn-{$size}" : ''),
]) }}>
    {{ $slot }}
</button>
