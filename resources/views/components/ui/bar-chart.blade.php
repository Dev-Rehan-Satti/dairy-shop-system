@props(['data' => [], 'ticks' => [], 'prefix' => 'Rs '])

<div class="flex gap-3">
    <div class="flex h-44 flex-col justify-between pb-5 pt-1 text-right text-[10px] text-base-content/40 tabular-nums">
        @foreach (array_reverse($ticks) as $tick)
            <span>{{ number_format($tick) }}</span>
        @endforeach
    </div>
    <div class="flex h-44 flex-1 items-end justify-between gap-2 border-b border-l border-base-200 pl-3">
        @foreach ($data as $bar)
            <div class="group flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                <span class="text-[11px] font-semibold text-base-content {{ $bar['isMax'] ? '' : 'invisible' }}">
                    {{ $prefix }}{{ number_format($bar['value']) }}
                </span>
                <div
                    class="w-full max-w-6 rounded-t bg-primary transition-opacity group-hover:opacity-80"
                    style="height: {{ $bar['pct'] }}%"
                    title="{{ $bar['label'] }}: {{ $prefix }}{{ number_format($bar['value']) }}"
                ></div>
                <span class="text-[10px] text-base-content/50">{{ $bar['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>
