<?php

use App\Models\CustomerPayment;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    public CustomerPayment $payment;

    public function mount(CustomerPayment $customerPayment): void
    {
        $this->payment = $customerPayment->load('customer');
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Customer Payments', 'href' => route('customer-payments.index')],
        ['label' => $payment->receipt_number],
    ]" class="print:hidden" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm print:hidden">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <a href="{{ route('customer-payments.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
                <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
            </a>
            <div>
                <h1 class="text-2xl font-bold text-base-content">Payment Receipt</h1>
                <p class="mt-1 text-sm text-base-content/60">Receipt {{ $payment->receipt_number }}</p>
            </div>
        </div>

        <button onclick="window.print()" class="btn btn-primary">
            <x-ui.icon name="file-text" class="size-4" />
            Print Receipt
        </button>
    </div>

    <div class="mx-auto max-w-xl">
        <x-ui.card class="print:border-none print:shadow-none">
            <div class="border-b border-dashed border-base-300 pb-4 text-center">
                <h2 class="text-lg font-bold text-base-content">{{ config('app.name') }}</h2>
                <p class="text-sm text-base-content/60">Payment Receipt</p>
            </div>

            <div class="flex items-center justify-between py-4 text-sm">
                <div>
                    <p class="text-base-content/50">Receipt No.</p>
                    <p class="font-mono font-medium text-base-content">{{ $payment->receipt_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-base-content/50">Date</p>
                    <p class="font-medium text-base-content">{{ $payment->payment_date->format('d M Y') }}</p>
                </div>
            </div>

            <div class="border-t border-dashed border-base-300 py-4 text-sm">
                <p class="text-base-content/50">Received From</p>
                <p class="font-medium text-base-content">{{ $payment->customer->name }}</p>
                <p class="text-base-content/60">{{ $payment->customer->mobile_number }}</p>
            </div>

            <div class="border-t border-dashed border-base-300 py-4">
                <p class="text-sm text-base-content/50">Amount Received</p>
                <p class="text-3xl font-bold text-base-content">{{ number_format($payment->amount, 2) }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 border-t border-dashed border-base-300 py-4 text-sm">
                <div>
                    <p class="text-base-content/50">Payment Method</p>
                    <p class="font-medium text-base-content">{{ $payment->methodLabel() }}</p>
                </div>
                @if ($payment->reference_number)
                    <div>
                        <p class="text-base-content/50">Reference No.</p>
                        <p class="font-medium text-base-content">{{ $payment->reference_number }}</p>
                    </div>
                @endif
                @if ($payment->account_details)
                    <div class="col-span-2">
                        <p class="text-base-content/50">{{ $payment->accountFieldLabel() }}</p>
                        <p class="font-medium text-base-content">{{ $payment->account_details }}</p>
                    </div>
                @endif
                @if ($payment->notes)
                    <div class="col-span-2">
                        <p class="text-base-content/50">Notes</p>
                        <p class="font-medium text-base-content">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="border-t border-dashed border-base-300 pt-4 text-center text-xs text-base-content/50">
                Thank you for your payment.
            </div>
        </x-ui.card>
    </div>
</div>
