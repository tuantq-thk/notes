<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @php
                        $user = auth()->user();
                        // $editorPermission = Spatie\Permission\Models\Permission::where('name', 'post_create')->first();
                        // $editorPermission->removeRole('editor');
                        // $editorPermission->syncRoles('editor');
                        // $user->givePermissionTo('post_show');
                        // $user->givePermissionTo('post_index');
                        // $user->givePermissionTo('post_create');
                        // $permissions = $user->getAllPermissions()->pluck('name');
                        // $roles = $user->getRoleNames();
                        // dump($permissions);
                        // die($roles);
                    @endphp
                    @can('post_create')
                        <a href="{{ route('posts.create') }}"
                            class="inline-flex items-center px-4 py-2 text-gray-800 border border-gray-300 rounded-md font-semibold text-xs uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Add new
                        </a>
                    @endcan
                    @foreach ($posts as $post)
                        <div class="card mt-10 w-full mb-5">
                            <div class="card-body flex items-center">
                                <h5 class="card-title">{{ $post->title }}</h5>
                                <div class="flex items-center gap-2">
                                    @can('post_show')
                                        <a href="{{ route('posts.show', $post) }}"
                                            class="p-2 rounded-md text-xs text-white bg-cyan-500 border">Show</a>
                                    @endcan
                                    @can('post_edit')
                                        <a href="{{ route('posts.edit', $post) }}"
                                            class="p-2 rounded-md text-xs text-white border bg-orange-300">
                                            Edit
                                        </a>
                                    @endcan
                                    @can('post_destroy')
                                        <form method="POST" action="{{ route('posts.destroy', $post) }}"
                                            style="display: inline"
                                            onsubmit="return confirm('Are you sure wanted to delete it?')">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit"
                                                class="p-2 rounded-md text-xs text-white bg-red-500 border">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan


                                </div>

                            </div>
                        </div>
                    @endforeach
                    <div class="mt-5 my-5">
                        {{ $posts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
