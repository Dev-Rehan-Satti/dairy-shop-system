<?php

use App\Models\Customer;
use App\Models\CustomerPayment;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public ?int $editingId = null;

    public string $customer_id = '';
    public string $amount = '';
    public string $method = 'cash';
    public string $payment_date = '';
    public string $account_details = '';
    public string $reference_number = '';
    public string $notes = '';

    public function mount(?CustomerPayment $customerPayment = null): void
    {
        $this->payment_date = now()->toDateString();

        if ($customerPayment) {
            $this->editingId = $customerPayment->id;
            $this->customer_id = (string) $customerPayment->customer_id;
            $this->amount = (string) $customerPayment->amount;
            $this->method = $customerPayment->method;
            $this->payment_date = $customerPayment->payment_date->toDateString();
            $this->account_details = (string) $customerPayment->account_details;
            $this->reference_number = (string) $customerPayment->reference_number;
            $this->notes = (string) $customerPayment->notes;
        } elseif (request()->query('customer_id')) {
            $this->customer_id = (string) request()->query('customer_id');
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', 'string', Rule::in(array_keys(CustomerPayment::METHODS))],
            'payment_date' => ['required', 'date'],
            'account_details' => ['nullable', 'string', 'max:255'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($this->editingId) {
            $payment = CustomerPayment::findOrFail($this->editingId);
            $payment->update($validated);
        } else {
            $validated['receipt_number'] = CustomerPayment::generateReceiptNumber();
            $payment = CustomerPayment::create($validated);
        }

        Customer::find($validated['customer_id'])?->refreshLastPayment();

        if (! $this->editingId) {
            $this->redirectRoute('customer-payments.receipt', $payment, navigate: true);

            return;
        }

        session()->flash('status', 'Payment updated successfully.');

        $this->redirectRoute('customer-payments.index', navigate: true);
    }

    public function with(): array
    {
        return [
            'customers' => Customer::orderBy('name')->get(),
            'methods' => CustomerPayment::METHODS,
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Customer Payments', 'href' => route('customer-payments.index')],
        ['label' => $editingId ? 'Edit Payment' : 'Record Payment'],
    ]" />

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('customer-payments.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
            <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $editingId ? 'Edit Payment' : 'Record Payment' }}</h1>
            <p class="mt-1 text-sm text-base-content/60">
                {{ $editingId ? 'Update this payment record.' : 'Enter the details of a payment received from a customer.' }}
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
                <x-ui.input type="number" step="0.01" min="0.01" label="Amount" wire:model="amount" placeholder="0.00" :error="$errors->first('amount')" />
                <x-ui.input type="date" label="Payment Date" wire:model="payment_date" :error="$errors->first('payment_date')" />
            </div>

            <label class="form-control w-full sm:max-w-xs">
                <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Payment Method</span>
                <select wire:model.live="method" class="select select-bordered w-full">
                    @foreach ($methods as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('method')
                    <span class="mt-1 text-xs text-error">{{ $message }}</span>
                @enderror
            </label>

            <div class="grid gap-6 sm:grid-cols-2">
                <x-ui.input
                    :label="match ($method) {
                        'bank' => 'Bank Account Details',
                        'jazzcash' => 'JazzCash Account Number',
                        'easypaisa' => 'Easypaisa Account Number',
                        default => 'Received By',
                    }"
                    wire:model="account_details"
                    :placeholder="match ($method) {
                        'bank' => 'e.g. HBL - 0123456789',
                        'jazzcash' => 'e.g. 0300-1234567',
                        'easypaisa' => 'e.g. 0300-1234567',
                        default => 'e.g. Shop counter',
                    }"
                    :error="$errors->first('account_details')"
                />
                <x-ui.input label="Reference / Transaction No." wire:model="reference_number" placeholder="Optional" :error="$errors->first('reference_number')" />
            </div>

            <label class="form-control w-full">
                <span class="label-text mb-1.5 text-sm font-medium text-base-content/80">Notes</span>
                <textarea wire:model="notes" rows="2" class="textarea textarea-bordered w-full" placeholder="Optional note about this payment"></textarea>
                @error('notes')
                    <span class="mt-1 text-xs text-error">{{ $message }}</span>
                @enderror
            </label>

            <div class="flex justify-end gap-2 border-t border-base-200 pt-6">
                <a href="{{ route('customer-payments.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Save Changes' : 'Save & Generate Receipt' }}</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>
    </x-ui.card>
</div>
