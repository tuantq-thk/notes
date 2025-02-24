<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Danh sách các quyền
        $permissions = [
            'post_index', 'post_create', 'post_edit', 'post_show', 'post_destroy',
            // 'category_index', 'category_create', 'category_edit', 'category_delete'
        ];

        // Tạo các quyền nếu chưa có
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Tạo Role "Admin" và gán tất cả quyền
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions);

        // Tạo Role "Editor" chỉ có quyền quản lý bài viết
        $editorRole = Role::firstOrCreate(['name' => 'editor']);
        $editorRole->syncPermissions(['post_index', 'post_create']);

        // Gán Role & Permission cho User (giả sử user ID = 1 là Admin)
        $adminUser = User::find(1);
        if ($adminUser) {
            $adminUser->assignRole('admin'); // Gán role Admin
        }

        // Tạo một user giả định làm Editor
        $editorUser = User::factory()->create([
            'name' => 'User',
            'email' => 'user@gmail.com',
            'password' => bcrypt('12341234'),
        ]);
        $editorUser->assignRole('editor');

        $this->command->info('Seeder Role-Permission done!');
    }
}
