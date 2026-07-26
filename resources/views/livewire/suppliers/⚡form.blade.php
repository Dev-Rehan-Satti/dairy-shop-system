<?php

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public ?int $editingId = null;

    public string $name = '';
    public string $mobile_number = '';
    public string $address = '';
    public string $status = 'active';

    public function mount(?Supplier $supplier = null): void
    {
        if ($supplier) {
            $this->editingId = $supplier->id;
            $this->name = $supplier->name;
            $this->mobile_number = $supplier->mobile_number;
            $this->address = (string) $supplier->address;
            $this->status = $supplier->status;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'string', Rule::in(array_keys(Supplier::STATUSES))],
        ]);

        Supplier::updateOrCreate(['id' => $this->editingId], $validated);

        session()->flash('status', $this->editingId ? 'Supplier updated successfully.' : 'Supplier created successfully.');

        $this->redirectRoute('suppliers.index', navigate: true);
    }

    public function with(): array
    {
        return [
            'statuses' => Supplier::STATUSES,
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Suppliers', 'href' => route('suppliers.index')],
        ['label' => $editingId ? 'Edit Supplier' : 'Add Supplier'],
    ]" />

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('suppliers.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
            <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $editingId ? 'Edit Supplier' : 'Add Supplier' }}</h1>
            <p class="mt-1 text-sm text-base-content/60">
                {{ $editingId ? 'Update this supplier\'s profile details.' : 'Create a new supplier profile.' }}
            </p>
        </div>
    </div>

    <x-ui.card>
        <form wire:submit="save" class="space-y-6">
            <x-ui.input label="Supplier Name" wire:model="name" placeholder="Ali Dairy Farm" :error="$errors->first('name')" />
            <x-ui.input label="Mobile Number" wire:model="mobile_number" placeholder="03xx-xxxxxxx" :error="$errors->first('mobile_number')" />

            <label class="form-control w-full">
                <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Address</span>
                <textarea wire:model="address" rows="2" class="textarea textarea-bordered w-full" placeholder="Village, city..."></textarea>
                @error('address')
                    <span class="mt-1 text-xs text-error">{{ $message }}</span>
                @enderror
            </label>

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
                <a href="{{ route('suppliers.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Supplier</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>
    </x-ui.card>
</div>
