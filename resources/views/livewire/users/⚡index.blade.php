<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::app')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $confirmingDeleteId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete(): void
    {
        $user = User::find($this->confirmingDeleteId);

        if (! $user || $user->id === auth()->id() || $user->hasRole('Super Admin')) {
            $this->confirmingDeleteId = null;

            return;
        }

        $user->delete();

        $this->confirmingDeleteId = null;
    }

    public function with(): array
    {
        return [
            'users' => User::query()
                ->with('roles')
                ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")))
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <x-ui.breadcrumb :items="[['label' => 'Home', 'href' => route('dashboard')], ['label' => 'Users']]" />

    @if (session('status'))
        <div class="alert alert-success mb-4 text-sm">
            <x-ui.icon name="check" class="size-4" />
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-base-content">Users</h1>
            <p class="mt-1 text-sm text-base-content/60">Manage user accounts and their assigned role.</p>
        </div>

        @can('users.create')
            <a href="{{ route('users.create') }}" wire:navigate class="btn btn-primary">
                <x-ui.icon name="plus" class="size-4" />
                Add User
            </a>
        @endcan
    </div>

    <x-ui.card>
        <div class="mb-4">
            <label class="input input-bordered flex w-full max-w-xs items-center gap-2">
                <x-ui.icon name="search" class="size-4 text-base-content/40" />
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search users..." class="grow" />
            </label>
        </div>

        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :name="$user->name" size="size-8" />
                                    <span class="font-medium text-base-content">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="text-base-content/70">{{ $user->email }}</td>
                            <td>
                                @forelse ($user->roles as $userRole)
                                    <x-ui.badge color="primary">{{ $userRole->name }}</x-ui.badge>
                                @empty
                                    <span class="text-sm text-base-content/40">—</span>
                                @endforelse
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @can('users.edit')
                                        <a href="{{ route('users.edit', $user) }}" wire:navigate class="btn btn-ghost btn-sm btn-circle" title="Edit">
                                            <x-ui.icon name="pencil" class="size-4" />
                                        </a>
                                    @endcan
                                    @can('users.delete')
                                        @unless ($user->hasRole('Super Admin'))
                                            <button wire:click="confirmDelete({{ $user->id }})" class="btn btn-ghost btn-sm btn-circle text-error" title="Delete">
                                                <x-ui.icon name="trash" class="size-4" />
                                            </button>
                                        @endunless
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-ui.empty-state icon="users" title="No users found" description="Try adjusting your search or add a new user." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-ui.card>

    <x-ui.modal :show="$confirmingDeleteId !== null" title="Delete user?" onClose="$set('confirmingDeleteId', null)" maxWidth="sm">
        <p class="text-sm text-base-content/70">This will permanently remove the user account. This action cannot be undone.</p>
        <div class="mt-6 flex justify-end gap-2">
            <x-ui.button variant="ghost" wire:click="$set('confirmingDeleteId', null)">Cancel</x-ui.button>
            <x-ui.button variant="error" wire:click="delete">Delete</x-ui.button>
        </div>
    </x-ui.modal>
</div>
