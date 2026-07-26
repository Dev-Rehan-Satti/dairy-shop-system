<?php

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Customers', 'href' => route('customers.index')],
        ['label' => $customer->name],
    ]" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
                <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
            </a>
            <x-ui.avatar :name="$customer->name" size="size-12" />
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-base-content">{{ $customer->name }}</h1>
                    <x-ui.badge :color="$customer->status === 'active' ? 'success' : 'neutral'">
                        {{ Customer::STATUSES[$customer->status] ?? $customer->status }}
                    </x-ui.badge>
                </div>
                <p class="mt-1 text-sm text-base-content/60">{{ $customer->typeLabel() }} &middot; {{ $customer->area ?: 'No area set' }}</p>
            </div>
        </div>

        @can('customers.edit')
            <a href="{{ route('customers.edit', $customer) }}" wire:navigate class="btn btn-primary">
                <x-ui.icon name="pencil" class="size-4" />
                Edit Customer
            </a>
        @endcan
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card
            icon="wallet"
            :color="$customer->current_balance > 0 ? 'error' : 'success'"
            :value="number_format($customer->current_balance, 2)"
            label="Current Balance"
        />
        <x-ui.stat-card
            icon="banknotes"
            color="primary"
            :value="$customer->last_payment_at ? number_format($customer->last_payment_amount, 2) : '—'"
            :label="$customer->last_payment_at ? 'Last Payment · '.$customer->last_payment_at->format('d M Y') : 'Last Payment'"
        />
        <x-ui.stat-card
            icon="truck"
            color="secondary"
            :value="$customer->last_delivery_at ? $customer->last_delivery_at->format('d M Y') : '—'"
            label="Last Delivery"
        />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.card title="Profile Details" class="lg:col-span-1">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-base-content/50">Mobile Number</dt>
                    <dd class="mt-0.5 flex items-center gap-1.5 font-medium text-base-content">
                        <x-ui.icon name="phone" class="size-4 text-base-content/40" />
                        {{ $customer->mobile_number }}
                    </dd>
                </div>
                <div>
                    <dt class="text-base-content/50">Address</dt>
                    <dd class="mt-0.5 font-medium text-base-content">{{ $customer->address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/50">Area</dt>
                    <dd class="mt-0.5 font-medium text-base-content">{{ $customer->area ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/50">Customer Type</dt>
                    <dd class="mt-0.5 font-medium text-base-content">{{ $customer->typeLabel() }}</dd>
                </div>
                <div>
                    <dt class="text-base-content/50">Customer Since</dt>
                    <dd class="mt-0.5 font-medium text-base-content">{{ $customer->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Order History" subtitle="Orders placed by this customer.">
                <x-ui.empty-state icon="clipboard" title="No orders yet" description="Order history will appear here once the orders module is in place." />
            </x-ui.card>

            <x-ui.card title="Payment History" subtitle="Payments received from this customer.">
                <x-ui.empty-state icon="credit-card" title="No payments yet" description="Payment history will appear here once the payments module is in place." />
            </x-ui.card>
        </div>
    </div>
</div>
