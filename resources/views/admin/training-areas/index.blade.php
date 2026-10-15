<x-layouts.admin title="Training areas">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.training-areas.create')" size="sm">Add training area</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Type', 'Capacity', 'Fee', 'Images', 'Status', '']" :empty="$trainingAreas->isEmpty()" empty-text="No training areas yet.">
        @foreach ($trainingAreas as $trainingArea)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $trainingArea->name }}</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($trainingArea->area_type) }}</td>
                <td class="px-4 py-3">{{ $trainingArea->min_participants }}–{{ $trainingArea->max_participants }} participants</td>
                <td class="px-4 py-3">{{ (float) $trainingArea->session_price > 0 ? $gym->currency.' '.number_format((float) $trainingArea->session_price, 0, ',', '.') : 'Free' }}</td>
                <td class="px-4 py-3">{{ $trainingArea->images_count }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$trainingArea->is_active ? 'success' : 'neutral'">{{ $trainingArea->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.training-areas.edit', $trainingArea) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.training-areas.destroy', $trainingArea)" message="Delete this training area?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
