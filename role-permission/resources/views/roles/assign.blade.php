<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Permissions') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="container mx-auto px-4 py-6">
                    <h2 class="text-2xl font-bold mb-4">Assign Permissions to Role: {{ $role->name }}</h2>

                    <form method="POST" action="{{ route('roles.permissions.store', $role) }}"
                        class="bg-white shadow-md rounded p-6">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700">Select Permissions</label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                            @if ($role->hasPermissionTo($permission->name)) checked @endif class="mr-2">
                                        {{ $permission->name }}
                                    </label>
                                @endforeach
                            </div>
                            @error('permissions')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Assign</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
