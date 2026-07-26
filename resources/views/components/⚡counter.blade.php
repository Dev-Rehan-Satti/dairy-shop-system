<?php

use Livewire\Component;

new class extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        $this->count--;
    }
};
?>

<div x-data="{ pulse: false }" x-effect="if ($wire.count) { pulse = true; setTimeout(() => pulse = false, 200) }" class="card w-96 bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title">Livewire + Alpine + DaisyUI + MySQL check</h2>

        <p class="text-2xl" :class="pulse ? 'text-success' : ''">
            Count: {{ $count }}
        </p>

        <div class="card-actions mt-2 gap-2">
            <button wire:click="decrement" class="btn btn-outline">-</button>
            <button wire:click="increment" class="btn btn-primary">+</button>
        </div>
    </div>
</div>