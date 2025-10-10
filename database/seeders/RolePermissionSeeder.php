<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        //Decommenter la ligne 14 en prod pour que les nouvelles permissions soient prises en compte
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $guard = 'sanctum';

        $permissions = collect([
            'agents.view',
            'agents.manage',
            'services.view',
            'services.manage',
            'demandes.view',
            'demandes.manage',
            'documents.view',
            'documents.manage',
            'users.view',
            'users.manage',
            'roles.view',
            'roles.manage',
            'permissions.view',
            'permissions.manage',
        ])->map(function (string $name) use ($guard) {
            return Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => $guard,
            ]);
        });

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);
        $verificateur = Role::firstOrCreate(['name' => 'verificateur', 'guard_name' => $guard]);
        $gestionnaire = Role::firstOrCreate(['name' => 'gestionnaire', 'guard_name' => $guard]);

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
