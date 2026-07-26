<?php

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new #[Layout('layouts::app')] class extends Component
{
    public ?int $editingId = null;

    public string $name = '';
    public ?string $description = null;
    /** @var array<int, string> */
    public array $selectedPermissions = [];

    public function mount(?Role $role = null): void
    {
        if ($role) {
            $this->editingId = $role->id;
            $this->name = $role->name;
            $this->description = $role->description;
            $this->selectedPermissions = $role->permissions->pluck('name')->all();
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:255'],
            'selectedPermissions' => ['array'],
        ]);

        $role = Role::updateOrCreate(
            ['id' => $this->editingId],
            ['name' => $validated['name'], 'description' => $validated['description']]
        );

        $role->syncPermissions($validated['selectedPermissions']);

        session()->flash('status', $this->editingId ? 'Role updated successfully.' : 'Role created successfully.');

        $this->redirectRoute('roles.index', navigate: true);
    }

    public function with(): array
    {
        return [
            'permissionGroups' => Permission::orderBy('name')->get()->groupBy(fn ($permission) => Str::before($permission->name, '.')),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[
        ['label' => 'Home', 'href' => route('dashboard')],
        ['label' => 'Roles', 'href' => route('roles.index')],
        ['label' => $editingId ? 'Edit Role' : 'Add Role'],
    ]" />

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('roles.index') }}" wire:navigate class="btn btn-ghost btn-circle btn-sm">
            <x-ui.icon name="chevron-right" class="size-4 rotate-180" />
        </a>
        <div>
            <h1 class="text-2xl font-bold text-base-content">{{ $editingId ? 'Edit Role' : 'Add Role' }}</h1>
            <p class="mt-1 text-sm text-base-content/60">
                {{ $editingId ? 'Update role details and permissions.' : 'Define a new role and assign its permissions.' }}
            </p>
        </div>
    </div>

    <x-ui.card>
        <form wire:submit="save" class="space-y-6">
            <div>
                <span class="mb-1 block text-xs font-semibold tracking-wider text-base-content/50 uppercase">Role Name</span>
                <input
                    type="text"
                    wire:model="name"
                    placeholder="e.g. Manager"
                    class="input input-bordered w-full {{ $errors->has('name') ? 'input-error' : '' }}"
                />
                @error('name')
                    <span class="mt-1 block text-xs text-error">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <span class="mb-1 block text-xs font-semibold tracking-wider text-base-content/50 uppercase">Description (optional)</span>
                <textarea
                    wire:model="description"
                    rows="3"
                    placeholder="What is this role for?"
                    class="textarea textarea-bordered w-full {{ $errors->has('description') ? 'textarea-error' : '' }}"
                ></textarea>
                @error('description')
                    <span class="mt-1 block text-xs text-error">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <span class="mb-2 block text-xs font-semibold tracking-wider text-base-content/50 uppercase">Permissions</span>
                <x-ui.permission-matrix :groups="$permissionGroups" model="selectedPermissions" />
            </div>

            <div class="flex justify-end gap-2 border-t border-base-200 pt-6">
                <a href="{{ route('roles.index') }}" wire:navigate class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save Role</span>
                    <span wire:loading wire:target="save" class="loading loading-spinner loading-sm"></span>
                </button>
            </div>
        </form>
    </x-ui.card>
</div>
