<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::create(['name' => 'admin']);
        $employer = Role::create(['name' => 'employer']);
        $user = Role::create(['name' => 'user']);

        // الصلاحيات (اختياري)
        $employer->givePermissionTo(['create job', 'edit job', 'delete job']);
        $user->givePermissionTo(['apply job']);
        $admin->givePermissionTo(Permission::all());
    }
}
