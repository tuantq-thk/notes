<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Permissions') }}
        </h2>
    </x-slot>
    <div class="container mx-auto px-4 py-6">
        <h2 class="text-2xl font-bold mb-4">Permission List</h2>

        <a href="{{ route('permissions.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Add Permission</a>

        <div class="bg-white shadow-md rounded mt-4 p-4">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-4 py-2">ID</th>
                        <th class="border px-4 py-2">Name</th>
                        <th class="border px-4 py-2">Assign</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissions as $permission)
                        <tr>
                            <td class="border px-4 py-2">{{ $permission->id }}</td>
                            <td class="border px-4 py-2">{{ $permission->name }}</td>
                            <td class="border px-4 py-2">
                                <a href="{{ route('roles.permissions', ['role' => 1]) }}"
                                    class="bg-green-500 text-white px-3 py-1 rounded">
                                    Assign
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
