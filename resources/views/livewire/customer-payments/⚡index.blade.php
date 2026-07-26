<?php

use App\Models\CustomerPayment;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $methodFilter = '';

    public ?int $confirmingDeleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMethodFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        $payment = CustomerPayment::find($this->confirmingDeleteId);
        $customer = $payment?->customer;

        $payment?->delete();
        $customer?->refreshLastPayment();

        $this->confirmingDeleteId = null;
    }

    public function with(): array
    {
        return [
            'payments' => CustomerPayment::query()
                ->with('customer')
                ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                    ->where('receipt_number', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->search}%"))))
                ->when($this->methodFilter, fn ($query) => $query->where('method', $this->methodFilter))
                ->latest('payment_date')
                ->latest('id')
                ->paginate(10),
            'methods' => CustomerPayment::METHODS,
            'todayTotal' => CustomerPayment::whereDate('payment_date', now()->toDateString())->sum('amount'),
            'monthTotal' => CustomerPayment::whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->sum('amount'),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[['label' => 'Home', 'href' => route('dashboard')], ['label' => 'Customer Payments']]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Customer Payments</h1>
            <p class="mt-1 text-sm text-base-content/60">Record payments and generate receipts.</p>
        </div>

        @can('customer_payments.create')
            <a href="{{ route('customer-payments.create') }}" wire:navigate class="btn btn-primary">
                <x-ui.icon name="plus" class="size-4" />
                Record Payment
            </a>
        @endcan
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-ui.stat-card icon="banknotes" color="primary" :value="number_format($todayTotal, 2)" label="Collected Today" />
        <x-ui.stat-card icon="wallet" color="secondary" :value="number_format($monthTotal, 2)" label="Collected This Month" />
    </div>

    <x-ui.card>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <label class="input input-bordered flex w-full max-w-xs items-center gap-2">
                <x-ui.icon name="search" class="size-4 text-base-content/40" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search receipt #, customer..." class="grow" />
            </label>

            <select wire:model.live="methodFilter" class="select select-bordered w-full max-w-[12rem]">
                <option value="">All methods</option>
                @foreach ($methods as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr wire:key="payment-{{ $payment->id }}">
                            <td class="font-mono text-sm text-base-content/70">{{ $payment->receipt_number }}</td>
                            <td class="font-medium text-base-content">{{ $payment->customer->name }}</td>
                            <td class="font-medium text-base-content">{{ number_format($payment->amount, 2) }}</td>
                            <td>
                                <x-ui.badge color="outline">{{ $payment->methodLabel() }}</x-ui.badge>
                            </td>
                            <td class="text-base-content/70">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('customer-payments.receipt', $payment) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="View Receipt">
                                        <x-ui.icon name="file-text" class="size-4" />
                                    </a>
                                    @can('customer_payments.edit')
                                        <a href="{{ route('customer-payments.edit', $payment) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <x-ui.icon name="pencil" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('customer_payments.delete')
                                        <button wire:click="confirmDelete({{ $payment->id }})" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                            <x-ui.icon name="trash" class="size-4" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-ui.empty-state icon="credit-card" title="No payments found" description="Try adjusting your search or record a new payment." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $payments->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal :show="$confirmingDeleteId !== null" title="Delete payment?" onClose="$set('confirmingDeleteId', null)" maxWidth="sm">
        <p class="text-sm text-base-content/70">This will permanently remove this payment record. This action cannot be undone.</p>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.button variant="ghost" wire:click="$set('confirmingDeleteId', null)">Cancel</x-ui.button>
            <x-ui.button variant="error" wire:click="delete">Delete</x-ui.button>
        </div>
    </x-ui.modal>
</div>
