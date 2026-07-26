<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new #[Layout('layouts::app')] class extends Component
{
    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public ?string $role = null;

    public function mount(?User $user = null): void
    {
        if ($user) {
            $this->editingId = $user->id;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->roles->first()?->name;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                ...(filled($validated['password']) ? ['password' => Hash::make($validated['password'])] : []),
            ]
        );

        $user->syncRoles([$validated['role']]);

        session()->flash('status', $this->editingId ? 'User updated successfully.' : 'User created successfully.');

        $this->redirectRoute('users.index', navigate: true);
    }

    public function with(): array
    {
        return [
            'roles' => Role::orderBy('name')->get(),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Users', 'href' => route('users.index')],
        ['label' => $editingId ? 'Edit User' : 'Add User'],
    ]" />

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('users.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
            <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $editingId ? 'Edit User' : 'Add User' }}</h1>
            <p class="mt-1 text-sm text-base-content/60">
                {{ $editingId ? 'Update account details and role.' : 'Create a new user account and assign a role.' }}
            </p>
        </div>
    </div>

    <x-ui.card>
        <form wire:submit="save" class="space-y-5">
            <x-ui.input label="Name" wire:model="name" placeholder="Jane Doe" :error="$errors->first('name')" />
            <x-ui.input label="Email" type="email" wire:model="email" placeholder="jane@example.com" :error="$errors->first('email')" />
            <x-ui.input
                label="Password"
                type="password"
                wire:model="password"
                :placeholder="$editingId ? 'Leave blank to keep current password' : '••••••••'"
                :error="$errors->first('password')"
            />

            <label class="form-control w-full">
                <span class="label-text mb-1 text-sm font-medium text-base-content/80">Role</span>
                <select wire:model="role" class="select select-bordered w-full">
                    <option value="">Select a role</option>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption->name }}">{{ $roleOption->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <span class="mt-1 text-xs text-error">{{ $message }}</span>
                @enderror
            </label>

            <div class="flex justify-end gap-2 border-t border-base-200 pt-6">
                <a href="{{ route('users.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save User</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>
    </x-ui.card>
</div>
