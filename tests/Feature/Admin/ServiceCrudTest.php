<?php

namespace Tests\Feature\Admin;

use App\Models\Institution;
use App\Models\Particulier;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_service(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin);

        $payload = [
            'nom' => 'Aide à domicile',
            'description' => 'Service d’accompagnement personnalisé.',
            'avantage' => ['Rapide', 'Fiable'],
            'delai' => '5 jours',
            'montant_min' => 150.50,
            'document_requis' => ['Pièce d’identité'],
        ];

        $response = $this->postJson('/api/backoffice/services', $payload);

        $response->assertCreated()
            ->assertJsonPath('nom', 'Aide à domicile')
            ->assertJsonPath('avantage', json_encode($payload['avantage'], JSON_UNESCAPED_UNICODE))
            ->assertJsonPath('document_requis', json_encode($payload['document_requis'], JSON_UNESCAPED_UNICODE))
            ->assertJsonPath('montant_min', 150.5);

        $this->assertDatabaseHas('services', [
            'nom' => 'Aide à domicile',
            'user_id' => $admin->id,
        ]);
    }

    public function test_non_admin_cannot_create_service(): void
    {
        $user = User::factory()->create();
        Particulier::create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/backoffice/services', [
            'nom' => 'Service Test',
            'description' => 'Description',
            'avantage' => ['Test'],
            'delai' => '5 jours',
            'montant_min' => 100,
            'document_requis' => ['Doc'],
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_update_service(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin);

        $service = Service::factory()->for($admin, 'owner')->create([
            'nom' => 'Ancien nom',
        ]);

        $response = $this->putJson("/api/backoffice/services/{$service->id}", [
            'nom' => 'Nouveau nom',
            'montant_min' => 250,
        ]);

        $response->assertOk()
            ->assertJsonPath('nom', 'Nouveau nom')
            ->assertJsonPath('montant_min', 250);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'nom' => 'Nouveau nom',
            'montant_min' => 250,
        ]);
    }

    public function test_admin_can_delete_service(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin);

        $service = Service::factory()->for($admin, 'owner')->create();

        $response = $this->deleteJson("/api/backoffice/services/{$service->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_index_returns_paginated_services(): void
    {
        $admin = $this->makeAdmin();
        Sanctum::actingAs($admin);

        Service::factory()->count(3)->for($admin, 'owner')->create();

        $response = $this->getJson('/api/services');

        $response->assertOk()
            ->assertJsonStructure(['data', 'current_page', 'last_page', 'per_page']);
    }

    public function test_non_admin_cannot_access_admin_service_listing(): void
    {
        $user = User::factory()->create();
        Particulier::create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/services')->assertForbidden();
    }

    public function test_unauthenticated_requests_receive_json_401(): void
    {
        $this->getJson('/api/services')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Authentification requise. Fournissez un jeton d\'accès valide.',
            ]);
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        Institution::create([
            'user_id' => $user->id,
            'nom_institution' => 'CDC',
            'type_institution' => 'institution_gouvernementale',
        ]);

        $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $user->syncRoles([$role]);

        return $user;
    }
}
