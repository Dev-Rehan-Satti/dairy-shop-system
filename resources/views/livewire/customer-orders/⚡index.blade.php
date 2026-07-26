<?php

use App\Models\CustomerOrder;
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
        CustomerOrder::find($this->confirmingDeleteId)?->delete();

        $this->confirmingDeleteId = null;
    }

    public function markDelivered(int $id): void
    {
        CustomerOrder::find($id)?->update(['status' => 'delivered']);

        session()->flash('status', 'Order marked as delivered.');
    }

    public function markCancelled(int $id): void
    {
        CustomerOrder::find($id)?->update(['status' => 'cancelled']);

        session()->flash('status', 'Order cancelled.');
    }

    protected function activeOrdersQuery()
    {
        $today = now()->toDateString();

        return CustomerOrder::where('status', '!=', 'cancelled')
            ->where(function ($query) use ($today) {
                $query->where('order_type', 'recurring')
                    ->orWhere(function ($query) use ($today) {
                        $query->where('order_type', 'one_time')->where('order_date', $today);
                    });
            });
    }

    public function with(): array
    {
        return [
            'orders' => CustomerOrder::query()
                ->with('customer')
                ->when($this->search, fn ($query) => $query->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%")))
                ->when($this->typeFilter, fn ($query) => $query->where('order_type', $this->typeFilter))
                ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
                ->latest()
                ->paginate(10),
            'types' => CustomerOrder::TYPES,
            'statuses' => CustomerOrder::STATUSES,
            'todayMorning' => $this->activeOrdersQuery()->sum('morning_quantity'),
            'todayEvening' => $this->activeOrdersQuery()->sum('evening_quantity'),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[['label' => 'Home', 'href' => route('dashboard')], ['label' => 'Customer Orders']]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Customer Orders</h1>
            <p class="mt-1 text-sm text-base-content/60">Manage each customer's daily milk requirement.</p>
        </div>

        @can('customer_orders.create')
            <a href="{{ route('customer-orders.create') }}" wire:navigate class="btn btn-primary">
                <x-ui.icon name="plus" class="size-4" />
                Add Order
            </a>
        @endcan
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2">
        <x-ui.stat-card icon="beaker" color="primary" :value="number_format($todayMorning, 2).' L'" label="Today's Morning Requirement" />
        <x-ui.stat-card icon="beaker" color="secondary" :value="number_format($todayEvening, 2).' L'" label="Today's Evening Requirement" />
    </div>

    <x-ui.card>
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <label class="input input-bordered flex w-full max-w-xs items-center gap-2">
                <x-ui.icon name="search" class="size-4 text-base-content/40" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search customer name..." class="grow" />
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
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Morning</th>
                        <th>Evening</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr wire:key="order-{{ $order->id }}">
                            <td class="font-medium text-base-content">{{ $order->customer->name }}</td>
                            <td>
                                <x-ui.badge :color="$order->order_type === 'recurring' ? 'primary' : 'secondary'">
                                    {{ $order->typeLabel() }}
                                </x-ui.badge>
                            </td>
                            <td class="text-base-content/70">{{ $order->morning_quantity > 0 ? number_format($order->morning_quantity, 2).' L' : '—' }}</td>
                            <td class="text-base-content/70">{{ $order->evening_quantity > 0 ? number_format($order->evening_quantity, 2).' L' : '—' }}</td>
                            <td class="text-base-content/70">{{ $order->order_type === 'recurring' ? 'Daily' : $order->order_date?->format('d M Y') }}</td>
                            <td>
                                <x-ui.badge :color="match ($order->status) {
                                    'delivered' => 'success',
                                    'cancelled' => 'neutral',
                                    default => 'warning',
                                }">
                                    {{ $statuses[$order->status] ?? $order->status }}
                                </x-ui.badge>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @can('customer_orders.edit')
                                        @if ($order->status === 'pending')
                                            <button wire:click="markDelivered({{ $order->id }})" class="btn btn-ghost btn-sm btn-circle text-success" title="Mark Delivered">
                                                <x-ui.icon name="check" class="size-4" />
                                            </button>
                                            <button wire:click="markCancelled({{ $order->id }})" class="btn btn-ghost btn-sm btn-circle text-error" title="Cancel Order">
                                                <x-ui.icon name="x" class="size-4" />
                                            </button>
                                        @endif
                                        <a href="{{ route('customer-orders.edit', $order) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <x-ui.icon name="pencil" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('customer_orders.delete')
                                        <button wire:click="confirmDelete({{ $order->id }})" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                            <x-ui.icon name="trash" class="size-4" />
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="clipboard" title="No orders found" description="Try adjusting your search or add a new order." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal :show="$confirmingDeleteId !== null" title="Delete order?" onClose="$set('confirmingDeleteId', null)" maxWidth="sm">
        <p class="text-sm text-base-content/70">This will permanently remove this order. This action cannot be undone.</p>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.button variant="ghost" wire:click="$set('confirmingDeleteId', null)">Cancel</x-ui.button>
            <x-ui.button variant="error" wire:click="delete">Delete</x-ui.button>
        </div>
    </x-ui.modal>
</div>
