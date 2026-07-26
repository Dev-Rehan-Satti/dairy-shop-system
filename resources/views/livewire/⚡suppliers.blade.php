<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::app')] class extends Component
{
    //
};
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-base-content">Suppliers</h1>
        <p class="mt-1 text-sm text-base-content/60">Manage milk suppliers and their collection records.</p>
    </div>

    <x-ui.card>
        <x-ui.empty-state
            icon="truck"
            title="Supplier profiles coming soon"
            description="This module will let you manage suppliers, deliveries, and payment history."
        />
    </x-ui.card>
</div>
