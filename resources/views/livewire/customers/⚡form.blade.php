<?php

use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public ?int $editingId = null;

    public string $name = '';
    public string $mobile_number = '';
    public string $address = '';
    public string $area = '';
    public string $customer_type = '';
    public string $status = 'active';

    public function mount(?Customer $customer = null): void
    {
        if ($customer) {
            $this->editingId = $customer->id;
            $this->name = $customer->name;
            $this->mobile_number = $customer->mobile_number;
            $this->address = (string) $customer->address;
            $this->area = (string) $customer->area;
            $this->customer_type = $customer->customer_type;
            $this->status = $customer->status;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'area' => ['nullable', 'string', 'max:255'],
            'customer_type' => ['required', 'string', Rule::in(array_keys(Customer::TYPES))],
            'status' => ['required', 'string', Rule::in(array_keys(Customer::STATUSES))],
        ]);

        Customer::updateOrCreate(['id' => $this->editingId], $validated);

        session()->flash('status', $this->editingId ? 'Customer updated successfully.' : 'Customer created successfully.');

        $this->redirectRoute('customers.index', navigate: true);
    }

    public function with(): array
    {
        return [
            'types' => Customer::TYPES,
            'statuses' => Customer::STATUSES,
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Customers', 'href' => route('customers.index')],
        ['label' => $editingId ? 'Edit Customer' : 'Add Customer'],
    ]" />

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('customers.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
            <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $editingId ? 'Edit Customer' : 'Add Customer' }}</h1>
            <p class="mt-1 text-sm text-base-content/60">
                {{ $editingId ? 'Update this customer\'s profile details.' : 'Create a new customer profile.' }}
            </p>
        </div>
    </div>

    <x-ui.card>
        <form wire:submit="save" class="space-y-6">
            <x-ui.input label="Customer Name" wire:model="name" placeholder="Ahmed Traders" :error="$errors->first('name')" />
            <x-ui.input label="Mobile Number" wire:model="mobile_number" placeholder="03xx-xxxxxxx" :error="$errors->first('mobile_number')" />

            <label class="form-control w-full">
                <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Address</span>
                <textarea wire:model="address" rows="2" class="textarea textarea-bordered w-full" placeholder="Street, city..."></textarea>
                @error('address')
                    <span class="mt-1 text-xs text-error">{{ $message }}</span>
                @enderror
            </label>

            <x-ui.input label="Area" wire:model="area" placeholder="e.g. Gulshan Block 5" :error="$errors->first('area')" />

            <div class="grid gap-6 sm:grid-cols-2">
                <label class="form-control w-full">
                    <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Customer Type</span>
                    <select wire:model="customer_type" class="select select-bordered w-full">
                        <option value="">Select a type</option>
                        @foreach ($types as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('customer_type')
                        <span class="mt-1 text-xs text-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="form-control w-full">
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
            </div>

            <div class="flex justify-end gap-2 border-t border-base-200 pt-6">
                <a href="{{ route('customers.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Customer</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>
    </x-ui.card>
</div>
