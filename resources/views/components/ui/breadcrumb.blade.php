@props(['items'])

<nav {{ $attributes->merge(['class' => 'mb-3 flex items-center gap-1.5 text-sm text-base-content/50']) }}>
    @foreach ($items as $item)
        @if (! $loop->last && ! empty($item['href']))
            <a href="{{ $item['href'] }}" wire:navigate class="hover:text-base-content">{{ $item['label'] }}</a>
        @else
            <span class="font-medium text-base-content">{{ $item['label'] }}</span>
        @endif

        @unless ($loop->last)
            <x-ui.icon name="chevron-right" class="size-3.5 text-base-content/30" />
        @endunless
    @endforeach
</nav>
