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

    public function with(): array
    {
        return [
            'orders' => $this->customer->orders()->latest()->take(5)->get(),
            'payments' => $this->customer->payments()->latest('payment_date')->latest('id')->take(5)->get(),
        ];
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
            <x-ui.card title="Order History" subtitle="Daily requirement and orders for this customer.">
                <x-slot:actions>
                    @can('customer_orders.create')
                        <a href="{{ route('customer-orders.create', ['customer_id' => $customer->id]) }}" wire:navigate class="btn btn-primary btn-sm">
                            <x-ui.icon name="plus" class="size-4" />
                            Add Order
                        </a>
                    @endcan
                </x-slot:actions>

                @if ($orders->isEmpty())
                    <x-ui.empty-state icon="clipboard" title="No orders yet" description="Add a recurring or one-time order for this customer." />
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Morning</th>
                                    <th>Evening</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr wire:key="order-{{ $order->id }}">
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
                                                {{ \App\Models\CustomerOrder::STATUSES[$order->status] ?? $order->status }}
                                            </x-ui.badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-right">
                        <a href="{{ route('customer-orders.index') }}" wire:navigate class="text-sm font-medium text-primary hover:underline">View all orders</a>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card title="Payment History" subtitle="Payments received from this customer.">
                <x-slot:actions>
                    @can('customer_payments.create')
                        <a href="{{ route('customer-payments.create', ['customer_id' => $customer->id]) }}" wire:navigate class="btn btn-primary btn-sm">
                            <x-ui.icon name="plus" class="size-4" />
                            Record Payment
                        </a>
                    @endcan
                </x-slot:actions>

                @if ($payments->isEmpty())
                    <x-ui.empty-state icon="credit-card" title="No payments yet" description="Record a payment received from this customer." />
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Receipt #</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr wire:key="payment-{{ $payment->id }}">
                                        <td class="font-mono text-sm text-base-content/70">{{ $payment->receipt_number }}</td>
                                        <td class="font-medium text-base-content">{{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            <x-ui.badge color="outline">{{ $payment->methodLabel() }}</x-ui.badge>
                                        </td>
                                        <td class="text-base-content/70">{{ $payment->payment_date->format('d M Y') }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('customer-payments.receipt', $payment) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="View Receipt">
                                                <x-ui.icon name="file-text" class="size-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 text-right">
                        <a href="{{ route('customer-payments.index') }}" wire:navigate class="text-sm font-medium text-primary hover:underline">View all payments</a>
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
