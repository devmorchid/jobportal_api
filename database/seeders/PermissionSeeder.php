<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::create(['name' => 'create job']);
        Permission::create(['name' => 'edit job']);
        Permission::create(['name' => 'delete job']);
        Permission::create(['name' => 'apply job']);
    }
}
