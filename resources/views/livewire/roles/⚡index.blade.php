<?php

use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $viewingPermissionsRoleId = null;
    public ?int $confirmingDeleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function viewPermissions(int $id): void
    {
        $this->viewingPermissionsRoleId = $id;
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        $role = Role::find($this->confirmingDeleteId);

        if ($role && $role->name !== 'Super Admin') {
            $role->delete();
        }

        $this->confirmingDeleteId = null;
    }

    public function with(): array
    {
        $viewingRole = $this->viewingPermissionsRoleId
            ? Role::with('permissions')->find($this->viewingPermissionsRoleId)
            : null;

        return [
            'roles' => Role::withCount(['permissions', 'users'])
                ->when($this->search, fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(10),
            'viewingRole' => $viewingRole,
            'viewingRolePermissionGroups' => $viewingRole
                ? $viewingRole->permissions->groupBy(fn ($permission) => Str::before($permission->name, '.'))
                : collect(),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[['label' => 'Home', 'href' => route('dashboard')], ['label' => 'Roles']]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Roles</h1>
            <p class="mt-1 text-sm text-base-content/60">Manage roles and the permissions assigned to them.</p>
        </div>

        @can('roles.create')
            <a href="{{ route('roles.create') }}" wire:navigate class="btn btn-primary">
                <x-ui.icon name="plus" class="size-4" />
                Add Role
            </a>
        @endcan
    </div>

    <x-ui.card>
        <div class="mb-4">
            <label class="input input-bordered flex w-full max-w-xs items-center gap-2">
                <x-ui.icon name="search" class="size-4 text-base-content/40" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search roles..." class="grow" />
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td class="font-medium text-base-content">{{ $role->name }}</td>
                            <td class="text-base-content/60">{{ $role->description ?: '—' }}</td>
                            <td>
                                <button
                                    wire:click="viewPermissions({{ $role->id }})"
                                    class="btn btn-outline btn-primary btn-sm gap-1.5"
                                >
                                    <x-ui.icon name="key" class="size-3.5" />
                                    {{ $role->permissions_count }}
                                </button>
                            </td>
                            <td class="text-base-content/70">{{ $role->users_count }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @can('roles.edit')
                                        <a href="{{ route('roles.edit', $role) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <x-ui.icon name="pencil" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('roles.delete')
                                        @if ($role->name !== 'Super Admin')
                                            <button wire:click="confirmDelete({{ $role->id }})" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                                <x-ui.icon name="trash" class="size-4" />
                                            </button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state icon="shield" title="No roles yet" description="Create a role to start assigning permissions." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal :show="$viewingRole !== null" :title="$viewingRole?->name" onClose="$set('viewingPermissionsRoleId', null)" maxWidth="xl">
        <p class="-mt-2 mb-4 text-sm text-base-content/60">Permissions assigned to this role.</p>

        <div class="max-h-96 space-y-4 overflow-y-auto">
            @forelse ($viewingRolePermissionGroups as $resource => $permissions)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-base-200 pb-3 last:border-0">
                    <p class="font-medium text-base-content">{{ Str::headline($resource) }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($permissions as $permission)
                            <span class="badge badge-outline badge-primary">
                                {{ Str::headline(Str::afterLast($permission->name, '.')) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-base-content/50">This role has no permissions assigned.</p>
            @endforelse
        </div>

        <div class="mt-6 flex justify-end">
            <x-ui.button variant="ghost" wire:click="$set('viewingPermissionsRoleId', null)">Close</x-ui.button>
        </div>
    </x-ui.modal>

    <x-ui.modal :show="$confirmingDeleteId !== null" title="Delete role?" onClose="$set('confirmingDeleteId', null)" maxWidth="sm">
        <p class="text-sm text-base-content/70">Users assigned to this role will lose the permissions it grants. This action cannot be undone.</p>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.button variant="ghost" wire:click="$set('confirmingDeleteId', null)">Cancel</x-ui.button>
            <x-ui.button variant="error" wire:click="delete">Delete</x-ui.button>
        </div>
    </x-ui.modal>
</div>
