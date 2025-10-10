<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            'agents.view',
            'agents.manage',
            'services.view',
            'services.manage',
            'demandes.view',
            'demandes.manage',
        ])->map(function (string $name) {
            return Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        });

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $verificateur = Role::firstOrCreate(['name' => 'verificateur', 'guard_name' => 'web']);
        $gestionnaire = Role::firstOrCreate(['name' => 'gestionnaire', 'guard_name' => 'web']);

        $superAdmin->syncPermissions($permissions);

        $verificateur->syncPermissions([
            'demandes.view',
            'demandes.manage',
        ]);

        $gestionnaire->syncPermissions([
            'services.view',
            'services.manage',
        ]);
    }
}
