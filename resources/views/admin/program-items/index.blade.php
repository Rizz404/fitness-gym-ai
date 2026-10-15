<x-layouts.admin title="Program">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.program-items.create')" size="sm">Add program item</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Category', 'Price', 'Status', '']" :empty="$fitnessPrograms->isEmpty()" empty-text="No program items yet.">
        @foreach ($fitnessPrograms as $fitnessProgram)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $fitnessProgram->name }}@if ($fitnessProgram->is_featured)<x-ui.badge tone="warning" class="ml-2">Featured</x-ui.badge>@endif</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($fitnessProgram->category) }}</td>
                <td class="px-4 py-3">{{ $gym->currency }} {{ number_format((float) $fitnessProgram->price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$fitnessProgram->is_active ? 'success' : 'neutral'">{{ $fitnessProgram->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                    @if ($fitnessProgram->is_sold_out)
                        <x-ui.badge tone="danger" class="ml-1">Sold out</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.program-items.edit', $fitnessProgram) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.program-items.destroy', $fitnessProgram)" message="Delete this program item?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
