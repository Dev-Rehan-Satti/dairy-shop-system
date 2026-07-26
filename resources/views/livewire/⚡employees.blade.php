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
        <h1 class="text-2xl font-bold text-base-content">Employees</h1>
        <p class="mt-1 text-sm text-base-content/60">Manage staff profiles and assignments.</p>
    </div>

    <x-ui.card>
        <x-ui.empty-state
            icon="user"
            title="Employee profiles coming soon"
            description="This module will let you manage staff records, roles, and shifts."
        />
    </x-ui.card>
</div>
