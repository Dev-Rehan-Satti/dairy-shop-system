<?php

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public Supplier $supplier;

    public bool $recordingPayment = false;

    public string $payment_amount = '';

    public function mount(Supplier $supplier): void
    {
        $this->supplier = $supplier;
    }

    public function recordPayment(): void
    {
        $validated = $this->validate([
            'payment_amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $this->supplier->increment('paid_amount', $validated['payment_amount']);
        $this->supplier->refresh();

        $this->recordingPayment = false;
        $this->payment_amount = '';

        session()->flash('status', 'Payment recorded successfully.');
    }

    public function with(): array
    {
        return [
            'totalMilk' => $this->supplier->totalMilk(),
            'totalAmount' => $this->supplier->totalAmount(),
            'remainingAmount' => $this->supplier->remainingAmount(),
            'collections' => $this->supplier->milkCollections()->latest('collection_date')->latest('session')->paginate(10),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Suppliers', 'href' => route('suppliers.index')],
        ['label' => $supplier->name],
    ]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('suppliers.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
                <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
            </a>
            <x-ui.avatar :name="$supplier->name" size="size-12" />
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-base-content">{{ $supplier->name }}</h1>
                    <x-ui.badge :color="$supplier->status === 'active' ? 'success' : 'neutral'">
                        {{ Supplier::STATUSES[$supplier->status] ?? $supplier->status }}
                    </x-ui.badge>
                </div>
                <p class="mt-1 text-sm text-base-content/60">{{ $supplier->mobile_number }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('suppliers.edit')
                <button wire:click="$set('recordingPayment', true)" class="btn btn-outline btn-primary">
                    <x-ui.icon name="banknotes" class="size-4" />
                    Record Payment
                </button>
                <a href="{{ route('suppliers.edit', $supplier) }}" wire:navigate class="btn btn-primary">
                    <x-ui.icon name="pencil" class="size-4" />
                    Edit Supplier
                </a>
            @endcan
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card
            icon="beaker"
            color="primary"
            :value="number_format($totalMilk, 2).' L'"
            label="Total Milk"
        />
        <x-ui.stat-card
            icon="wallet"
            color="secondary"
            :value="number_format($totalAmount, 2)"
            label="Total Amount"
        />
        <x-ui.stat-card
            icon="banknotes"
            color="success"
            :value="number_format($supplier->paid_amount, 2)"
            label="Paid"
        />
        <x-ui.stat-card
            icon="clock"
            :color="$remainingAmount > 0 ? 'error' : 'success'"
            :value="number_format($remainingAmount, 2)"
            label="Remaining"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Profile Details" class="lg:col-span-1">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-base-content/50">Mobile Number</dt>
                    <dd class="mt-0.5 flex items-center gap-1.5 font-medium text-base-content">
                        <x-ui.icon name="phone" class="size-4 text-base-content/40" />
                        {{ $supplier->mobile_number }}
                    </dd>
                </div>
                <div>
                    <dt class="text-base-content/50">Address</dt>
                    <dd class="mt-0.5 font-medium text-base-content">{{ $supplier->address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/50">Supplier Since</dt>
                    <dd class="mt-0.5 font-medium text-base-content">{{ $supplier->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Collection History" subtitle="Milk collected from this supplier.">
                @if ($collections->isEmpty())
                    <x-ui.empty-state icon="beaker" title="No collections yet" description="Collection records will appear here once milk is recorded for this supplier." />
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Session</th>
                                    <th>Quantity</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($collections as $collection)
                                    <tr wire:key="collection-{{ $collection->id }}">
                                        <td>{{ $collection->collection_date->format('d M Y') }}</td>
                                        <td>
                                            <x-ui.badge color="outline">{{ \App\Models\MilkCollection::SESSIONS[$collection->session] ?? $collection->session }}</x-ui.badge>
                                        </td>
                                        <td>{{ number_format($collection->quantity, 2) }} L</td>
                                        <td>{{ number_format($collection->rate, 2) }}</td>
                                        <td class="font-medium">{{ number_format($collection->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $collections->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>

    <x-ui.modal :show="$recordingPayment" title="Record payment" onClose="$set('recordingPayment', false)" maxWidth="sm">
        <form wire:submit="recordPayment" class="space-y-4">
            <x-ui.input label="Payment Amount" type="number" step="0.01" min="0.01" wire:model="payment_amount" placeholder="0.00" :error="$errors->first('payment_amount')" />
            <div class="flex justify-end gap-2">
                <x-ui.button variant="ghost" wire:click="$set('recordingPayment', false)">Cancel</x-ui.button>
                <button type="submit" class="btn btn-primary">Save Payment</button>
            </div>
        </form>
    </x-ui.modal>
</div>
