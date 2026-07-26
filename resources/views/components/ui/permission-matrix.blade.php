@props(['groups', 'model' => 'selectedPermissions'])

@php
    use Illuminate\Support\Str;

    $actionLabels = ['create' => 'Add', 'view' => 'View', 'edit' => 'Edit', 'delete' => 'Delete'];
    $preferredOrder = ['create', 'view', 'edit', 'delete'];

    $allActions = $groups->flatMap(fn ($permissions) => $permissions->map(fn ($p) => str($p->name)->afterLast('.')->toString()))->unique();

    $orderedActions = collect($preferredOrder)
        ->filter(fn ($action) => $allActions->contains($action))
        ->concat($allActions->diff($preferredOrder)->sort()->values())
        ->values();
@endphp

<div class="overflow-x-auto rounded-lg border border-base-200">
    <table class="table">
        <thead>
            <tr>
                <th>Resource</th>
                @foreach ($orderedActions as $action)
                    <th class="text-center">{{ $actionLabels[$action] ?? Str::headline($action) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $resource => $permissions)
                <tr>
                    <td class="font-medium text-base-content">{{ Str::headline($resource) }}</td>
                    @foreach ($orderedActions as $action)
                        @php $permissionName = "$resource.$action"; @endphp
                        <td class="text-center">
                            @if ($permissions->contains('name', $permissionName))
                                <input
                                    type="checkbox"
                                    wire:model="{{ $model }}"
                                    value="{{ $permissionName }}"
                                    class="checkbox checkbox-sm checkbox-primary"
                                />
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $orderedActions->count() + 1 }}" class="py-6 text-center text-sm text-base-content/50">
                        No permissions defined yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
