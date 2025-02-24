<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Role List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container mx-auto px-4 py-6">
                    <h2 class="text-2xl font-bold mb-4">Role List</h2>

                    <a href="{{ route('roles.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">Add Role</a>

                    <div class="bg-white shadow-md rounded mt-4 p-4">
                        <table class="w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border px-4 py-2">ID</th>
                                    <th class="border px-4 py-2">Name</th>
                                    <th class="border px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td class="border px-4 py-2">{{ $role->id }}</td>
                                        <td class="border px-4 py-2">{{ $role->name }}</td>
                                        <td class="border px-4 py-2">
                                            <a href="{{ route('roles.permissions', $role) }}"
                                                class="bg-green-500 text-white px-3 py-1 rounded">
                                                Assign Permissions
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
