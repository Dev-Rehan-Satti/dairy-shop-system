<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::guest')] class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', 'These credentials do not match our records.');

            return;
        }

        request()->session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }
};
?>

<div>
    <h1 class="text-2xl font-bold text-base-content">Welcome back</h1>
    <p class="mt-1 text-sm text-base-content/60">Enter your email and password to access your account.</p>

    <form wire:submit="login" class="mt-8 space-y-4">
        <x-ui.input
            label="Email"
            type="email"
            wire:model="email"
            placeholder="you@example.com"
            autofocus
            :error="$errors->first('email')"
        />

        <x-ui.input
            label="Password"
            type="password"
            wire:model="password"
            placeholder="••••••••"
            :error="$errors->first('password')"
        />

        <div class="flex items-center justify-between pt-2 text-sm">
            <label class="flex cursor-pointer items-center gap-2">
                <input type="checkbox" wire:model="remember" class="checkbox checkbox-sm" />
                <span class="text-base-content/70">Remember me</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">Log in</span>
            <span wire:loading wire:target="login" class="loading loading-spinner loading-sm"></span>
        </button>
    </form>
</div>
