<?php

use App\Models\Customer;
use App\Models\CustomerOrder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public ?int $editingId = null;

    public string $customer_id = '';
    public string $order_type = 'recurring';
    public string $order_date = '';
    public string $morning_quantity = '';
    public string $evening_quantity = '';
    public string $status = 'pending';

    public function mount(?CustomerOrder $customerOrder = null): void
    {
        if ($customerOrder) {
            $this->editingId = $customerOrder->id;
            $this->customer_id = (string) $customerOrder->customer_id;
            $this->order_type = $customerOrder->order_type;
            $this->order_date = $customerOrder->order_date?->toDateString() ?? '';
            $this->morning_quantity = $customerOrder->morning_quantity > 0 ? (string) $customerOrder->morning_quantity : '';
            $this->evening_quantity = $customerOrder->evening_quantity > 0 ? (string) $customerOrder->evening_quantity : '';
            $this->status = $customerOrder->status;
        } elseif (request()->query('customer_id')) {
            $this->customer_id = (string) request()->query('customer_id');
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'order_type' => ['required', 'string', Rule::in(array_keys(CustomerOrder::TYPES))],
            'order_date' => ['nullable', 'date', 'required_if:order_type,one_time'],
            'morning_quantity' => ['nullable', 'numeric', 'min:0'],
            'evening_quantity' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', Rule::in(array_keys(CustomerOrder::STATUSES))],
        ]);

        if ((float) ($validated['morning_quantity'] ?: 0) <= 0 && (float) ($validated['evening_quantity'] ?: 0) <= 0) {
            $this->addError('morning_quantity', 'Enter at least a morning or evening quantity.');

            return;
        }

        $validated['morning_quantity'] = $validated['morning_quantity'] ?: 0;
        $validated['evening_quantity'] = $validated['evening_quantity'] ?: 0;
        $validated['order_date'] = $validated['order_type'] === 'one_time' ? $validated['order_date'] : null;

        CustomerOrder::updateOrCreate(['id' => $this->editingId], $validated);

        session()->flash('status', $this->editingId ? 'Order updated successfully.' : 'Order created successfully.');

        $this->redirectRoute('customer-orders.index', navigate: true);
    }

    public function with(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'types' => CustomerOrder::TYPES,
            'statuses' => CustomerOrder::STATUSES,
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Customer Orders', 'href' => route('customer-orders.index')],
        ['label' => $editingId ? 'Edit Order' : 'Add Order'],
    ]" />

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('customer-orders.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
            <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $editingId ? 'Edit Order' : 'Add Order' }}</h1>
            <p class="mt-1 text-sm text-base-content/60">
                {{ $editingId ? 'Update this customer\'s daily requirement.' : 'Set up a customer\'s daily milk requirement.' }}
            </p>
        </div>
    </div>

    <x-ui.card>
        <form wire:submit="save" class="space-y-6">
            <label class="form-control w-full">
                <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Customer</span>
                <select wire:model="customer_id" class="select select-bordered w-full">
                    <option value="">Select a customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                @error('customer_id')
                    <span class="mt-1 text-xs text-error">{{ $message }}</span>
                @enderror
            </label>

            <div class="grid gap-6 sm:grid-cols-2">
                <label class="form-control w-full">
                    <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Order Type</span>
                    <select wire:model.live="order_type" class="select select-bordered w-full">
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('order_type')
                        <span class="mt-1 text-xs text-error">{{ $message }}</span>
                    @enderror
                </label>

                @if ($order_type === 'one_time')
                    <x-ui.input type="date" label="Order Date" wire:model="order_date" :error="$errors->first('order_date')" />
                @endif
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <x-ui.input type="number" step="0.01" min="0" label="Morning Quantity (Liters)" wire:model="morning_quantity" placeholder="e.g. 2" :error="$errors->first('morning_quantity')" />
                <x-ui.input type="number" step="0.01" min="0" label="Evening Quantity (Liters)" wire:model="evening_quantity" placeholder="e.g. 1" :error="$errors->first('evening_quantity')" />
            </div>

            <label class="form-control w-full sm:max-w-xs">
                <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Status</span>
                <select wire:model="status" class="select select-bordered w-full">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')
                    <span class="mt-1 text-xs text-error">{{ $message }}</span>
                @enderror
            </label>

            <div class="flex justify-end gap-2 border-t border-base-200 pt-6">
                <a href="{{ route('customer-orders.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Order</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>
    </x-ui.card>
</div>
