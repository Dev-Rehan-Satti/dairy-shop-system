<?php

use App\Models\MilkCollection;
use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public string $date = '';

    public array $morning = [];

    public array $evening = [];

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->loadCollections();
    }

    public function updatedDate(): void
    {
        $this->loadCollections();
    }

    protected function loadCollections(): void
    {
        $suppliers = Supplier::where('status', 'active')->get(['id']);

        $existing = MilkCollection::where('collection_date', $this->date)
            ->get()
            ->groupBy('session');

        foreach (['morning', 'evening'] as $session) {
            $records = $existing->get($session, collect())->keyBy('supplier_id');
            $rows = [];

            foreach ($suppliers as $supplier) {
                $record = $records->get($supplier->id);
                $rows[$supplier->id] = [
                    'quantity' => $record ? (string) $record->quantity : '',
                    'rate' => $record ? (string) $record->rate : '',
                ];
            }

            $this->{$session} = $rows;
        }
    }

    public function saveSession(string $session): void
    {
        foreach ($this->{$session} as $supplierId => $row) {
            $quantity = (float) ($row['quantity'] ?? 0);
            $rate = (float) ($row['rate'] ?? 0);

            if ($quantity <= 0 || $rate <= 0) {
                MilkCollection::where([
                    'supplier_id' => $supplierId,
                    'collection_date' => $this->date,
                    'session' => $session,
                ])->delete();

                continue;
            }

            MilkCollection::updateOrCreate(
                ['supplier_id' => $supplierId, 'collection_date' => $this->date, 'session' => $session],
                ['quantity' => $quantity, 'rate' => $rate, 'amount' => $quantity * $rate]
            );
        }

        session()->flash('status', ucfirst($session).' collection saved successfully.');
    }

    public function with(): array
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return [
            'suppliers' => $suppliers,
            'dailyTotalMilk' => MilkCollection::where('collection_date', $this->date)->sum('quantity'),
            'dailyTotalAmount' => MilkCollection::where('collection_date', $this->date)->sum('amount'),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[['label' => 'Home', 'href' => route('dashboard')], ['label' => 'Milk Collection']]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Milk Collection</h1>
            <p class="mt-1 text-sm text-base-content/60">Record morning and evening milk collection for each supplier.</p>
        </div>

        <label class="input input-bordered flex items-center gap-2">
            <x-ui.icon name="clock" class="size-4 text-base-content/40" />
            <input type="date" wire:model.live="date" class="grow" />
        </label>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-ui.stat-card icon="beaker" color="primary" :value="number_format($dailyTotalMilk, 2).' L'" label="Daily Total Milk Received" />
        <x-ui.stat-card icon="wallet" color="secondary" :value="number_format($dailyTotalAmount, 2)" label="Daily Total Amount" />
    </div>

    @if ($suppliers->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="truck" title="No active suppliers" description="Add an active supplier before recording milk collection." />
        </x-ui.card>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach (['morning' => 'Morning Collection', 'evening' => 'Evening Collection'] as $session => $sessionLabel)
                <x-ui.card :title="$sessionLabel">
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Supplier</th>
                                    <th>Quantity (L)</th>
                                    <th>Rate</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($suppliers as $supplier)
                                    <tr wire:key="{{ $session }}-{{ $supplier->id }}">
                                        <td class="font-medium text-base-content">{{ $supplier->name }}</td>
                                        <td>
                                            <input
                                                type="number" step="0.01" min="0"
                                                wire:model.live.debounce.500ms="{{ $session }}.{{ $supplier->id }}.quantity"
                                                class="input input-bordered input-sm w-24"
                                            />
                                        </td>
                                        <td>
                                            <input
                                                type="number" step="0.01" min="0"
                                                wire:model.live.debounce.500ms="{{ $session }}.{{ $supplier->id }}.rate"
                                                class="input input-bordered input-sm w-24"
                                            />
                                        </td>
                                        <td class="font-medium text-base-content">
                                            @php $row = (${$session})[$supplier->id] ?? []; @endphp
                                            {{ number_format((float) ($row['quantity'] ?? 0) * (float) ($row['rate'] ?? 0), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @can('milk_collections.create')
                        <div class="mt-4 flex justify-end border-t border-base-200 pt-4">
                            <button wire:click="saveSession('{{ $session }}')" wire:loading.attr="disabled" wire:target="saveSession('{{ $session }}')" class="btn btn-primary btn-sm">
                                <span wire:loading.remove wire:target="saveSession('{{ $session }}')">Save {{ $sessionLabel }}</span>
                                <span wire:loading wire:target="saveSession('{{ $session }}')" class="loading loading-spinner loading-xs"></span>
                            </button>
                        </div>
                    @endcan
                </x-ui.card>
            @endforeach
        </div>
    @endif
</div>
