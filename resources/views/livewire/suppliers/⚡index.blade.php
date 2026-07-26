<?php

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public ?int $confirmingDeleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        Supplier::find($this->confirmingDeleteId)?->delete();

        $this->confirmingDeleteId = null;
    }

    public function with(): array
    {
        return [
            'suppliers' => Supplier::query()
                ->withSum('milkCollections as total_milk', 'quantity')
                ->withSum('milkCollections as total_amount', 'amount')
                ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('mobile_number', 'like', "%{$this->search}%")))
                ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
                ->latest()
                ->paginate(10),
            'statuses' => Supplier::STATUSES,
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[['label' => 'Home', 'href' => route('dashboard')], ['label' => 'Suppliers']]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Suppliers</h1>
            <p class="mt-1 text-sm text-base-content/60">Manage milk suppliers and their ledger.</p>
        </div>

        @can('suppliers.create')
            <a href="{{ route('suppliers.create') }}" wire:navigate class="btn btn-primary">
                <x-ui.icon name="plus" class="size-4" />
                Add Supplier
            </a>
        @endcan
    </div>

    <x-ui.card>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <label class="input input-bordered flex w-full max-w-xs items-center gap-2">
                <x-ui.icon name="search" class="size-4 text-base-content/40" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, mobile..." class="grow" />
            </label>

            <select wire:model.live="statusFilter" class="select select-bordered w-full max-w-[10rem]">
                <option value="">All statuses</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Total Milk</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        @php $remaining = ($supplier->total_amount ?? 0) - $supplier->paid_amount; @endphp
                        <tr wire:key="supplier-{{ $supplier->id }}">
                            <td>
                                <a href="{{ route('suppliers.show', $supplier) }}" wire:navigate class="flex items-center gap-3 hover:underline">
                                    <x-ui.avatar :name="$supplier->name" size="size-8" />
                                    <span class="font-medium text-base-content">{{ $supplier->name }}</span>
                                </a>
                            </td>
                            <td class="text-base-content/70">{{ $supplier->mobile_number }}</td>
                            <td class="text-base-content/70">{{ number_format($supplier->total_milk ?? 0, 2) }} L</td>
                            <td class="text-base-content/70">{{ number_format($supplier->total_amount ?? 0, 2) }}</td>
                            <td class="text-base-content/70">{{ number_format($supplier->paid_amount, 2) }}</td>
                            <td class="{{ $remaining > 0 ? 'text-error' : 'text-base-content/70' }}">
                                {{ number_format($remaining, 2) }}
                            </td>
                            <td>
                                <x-ui.badge :color="$supplier->status === 'active' ? 'success' : 'neutral'">
                                    {{ $statuses[$supplier->status] ?? $supplier->status }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('suppliers.show', $supplier) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="View">
                                        <x-ui.icon name="user" class="size-4" />
                                    </a>
                                    @can('suppliers.edit')
                                        <a href="{{ route('suppliers.edit', $supplier) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <x-ui.icon name="pencil" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('suppliers.delete')
                                        <button wire:click="confirmDelete({{ $supplier->id }})" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                            <x-ui.icon name="trash" class="size-4" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-ui.empty-state icon="truck" title="No suppliers found" description="Try adjusting your search or add a new supplier." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $suppliers->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal :show="$confirmingDeleteId !== null" title="Delete supplier?" onClose="$set('confirmingDeleteId', null)" maxWidth="sm">
        <p class="text-sm text-base-content/70">This will permanently remove the supplier profile and its collection history. This action cannot be undone.</p>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.button variant="ghost" wire:click="$set('confirmingDeleteId', null)">Cancel</x-ui.button>
            <x-ui.button variant="error" wire:click="delete">Delete</x-ui.button>
        </div>
    </x-ui.modal>
</div>
