<?php

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public ?int $confirmingDeleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
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
        Customer::find($this->confirmingDeleteId)?->delete();

        $this->confirmingDeleteId = null;
    }

    public function with(): array
    {
        return [
            'customers' => Customer::query()
                ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('mobile_number', 'like', "%{$this->search}%")
                    ->orWhere('area', 'like', "%{$this->search}%")))
                ->when($this->typeFilter, fn ($query) => $query->where('customer_type', $this->typeFilter))
                ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
                ->latest()
                ->paginate(10),
            'types' => Customer::TYPES,
            'statuses' => Customer::STATUSES,
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[['label' => 'Home', 'href' => route('dashboard')], ['label' => 'Customers']]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Customers</h1>
            <p class="mt-1 text-sm text-base-content/60">Manage customer profiles, balances, and delivery history.</p>
        </div>

        @can('customers.create')
            <a href="{{ route('customers.create') }}" wire:navigate class="btn btn-primary">
                <x-ui.icon name="plus" class="size-4" />
                Add Customer
            </a>
        @endcan
    </div>

    <x-ui.card>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <label class="input input-bordered flex w-full max-w-xs items-center gap-2">
                <x-ui.icon name="search" class="size-4 text-base-content/40" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search name, mobile, area..." class="grow" />
            </label>

            <select wire:model.live="typeFilter" class="select select-bordered w-full max-w-[12rem]">
                <option value="">All types</option>
                @foreach ($types as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

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
                        <th>Area</th>
                        <th>Type</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}">
                            <td>
                                <a href="{{ route('customers.show', $customer) }}" wire:navigate class="flex items-center gap-3 hover:underline">
                                    <x-ui.avatar :name="$customer->name" size="size-8" />
                                    <span class="font-medium text-base-content">{{ $customer->name }}</span>
                                </a>
                            </td>
                            <td class="text-base-content/70">{{ $customer->mobile_number }}</td>
                            <td class="text-base-content/70">{{ $customer->area ?: '—' }}</td>
                            <td>
                                <x-ui.badge color="primary">{{ $customer->typeLabel() }}</x-ui.badge>
                            </td>
                            <td class="{{ $customer->current_balance > 0 ? 'text-error' : 'text-base-content/70' }}">
                                {{ number_format($customer->current_balance, 2) }}
                            </td>
                            <td>
                                <x-ui.badge :color="$customer->status === 'active' ? 'success' : 'neutral'">
                                    {{ $statuses[$customer->status] ?? $customer->status }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('customers.show', $customer) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="View">
                                        <x-ui.icon name="user" class="size-4" />
                                    </a>
                                    @can('customers.edit')
                                        <a href="{{ route('customers.edit', $customer) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <x-ui.icon name="pencil" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('customers.delete')
                                        <button wire:click="confirmDelete({{ $customer->id }})" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                            <x-ui.icon name="trash" class="size-4" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="users" title="No customers found" description="Try adjusting your search or add a new customer." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $customers->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal :show="$confirmingDeleteId !== null" title="Delete customer?" onClose="$set('confirmingDeleteId', null)" maxWidth="sm">
        <p class="text-sm text-base-content/70">This will permanently remove the customer profile and its history. This action cannot be undone.</p>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.button variant="ghost" wire:click="$set('confirmingDeleteId', null)">Cancel</x-ui.button>
            <x-ui.button variant="error" wire:click="delete">Delete</x-ui.button>
        </div>
    </x-ui.modal>
</div>
