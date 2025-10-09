<?php

namespace Tests\Feature\Demande;

use App\Models\Demande;
use App\Models\Document;
use App\Models\Institution;
use App\Models\Particulier;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DemandeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_particulier_can_create_demande_with_documents(): void
    {
        Storage::fake('public');

        $serviceOwner = $this->makeAdmin();
        $service = Service::factory()->for($serviceOwner, 'owner')->create();

        $user = User::factory()->create();
        Particulier::create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $payload = [
            'type_demande' => 'particulier',
            'description' => 'Besoin d’un accompagnement.',
            'urgent' => true,
            'service_id' => $service->id,
            'documents' => [
                UploadedFile::fake()->create('piece.pdf', 200),
                UploadedFile::fake()->image('photo.jpg'),
            ],
            'documents_meta' => [
                ['titre' => 'Pièce officielle'],
                ['titre' => 'Photo'],
            ],
        ];

        $response = $this->postJson('/api/demandes', $payload);

        $response->assertCreated()
            ->assertJsonPath('type_demande', 'Particulier')
            ->assertJsonPath('documents.0.titre', 'Pièce officielle');

        $demande = Demande::first();
        $this->assertNotNull($demande);
        $this->assertTrue($demande->urgent);
        $this->assertCount(2, $demande->documents);
        Storage::disk('public')->assertExists($demande->documents->first()->path);
    }

    public function test_user_cannot_create_demande_with_invalid_type(): void
    {
        $serviceOwner = $this->makeAdmin();
        $service = Service::factory()->for($serviceOwner, 'owner')->create();

        $user = User::factory()->create();
        Particulier::create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/demandes', [
            'type_demande' => 'entreprise',
            'description' => 'Invalide',
            'service_id' => $service->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('type_demande');
    }

    public function test_user_can_update_demande_and_manage_documents(): void
    {
        Storage::fake('public');

        $serviceOwner = $this->makeAdmin();
        $service = Service::factory()->for($serviceOwner, 'owner')->create();

        $user = User::factory()->create();
        Particulier::create(['user_id' => $user->id]);

        $demande = Demande::factory()
            ->for($user)
            ->for($service)
            ->create([
                'type_demande' => 'Particulier',
                'description' => 'Ancienne description',
                'urgent' => false,
                'status' => 'soumission',
            ]);

        $document = $demande->documents()->create([
            'titre' => 'Ancien',
            'path' => UploadedFile::fake()->create('old.pdf')->store('documents', 'public'),
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/demandes/{$demande->id}", [
            'description' => 'Nouvelle description',
            'urgent' => true,
            'documents' => [UploadedFile::fake()->create('new.pdf', 100)],
            'documents_meta' => [['titre' => 'Nouveau']],
            'documents_to_remove' => [$document->id],
        ]);

        $response->assertOk()
            ->assertJsonPath('description', 'Nouvelle description')
            ->assertJsonCount(1, 'documents');

        Storage::disk('public')->assertMissing($document->path);
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_admin_can_view_all_demandes(): void
    {
        $admin = $this->makeAdmin();
        $service = Service::factory()->for($admin, 'owner')->create();

        $user = User::factory()->create();
        Particulier::create(['user_id' => $user->id]);

        Demande::factory()->count(2)->for($user)->for($service)->create([
            'type_demande' => 'Particulier',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/demandes');

        $response->assertOk()
            ->assertJsonPath('total', 2);
    }

    public function test_owner_can_view_single_demande(): void
    {
        $serviceOwner = $this->makeAdmin();
        $service = Service::factory()->for($serviceOwner, 'owner')->create();

        $user = User::factory()->create();
        Particulier::create(['user_id' => $user->id]);

        $demande = Demande::factory()->for($user)->for($service)->create([
            'type_demande' => 'Particulier',
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/demandes/{$demande->id}")
            ->assertOk()
            ->assertJsonPath('id', $demande->id);
    }

    private function makeAdmin(): User
    {
        $user = User::factory()->create();

        Institution::create([
            'user_id' => $user->id,
            'nom_institution' => 'CDC',
            'type_institution' => 'institution_gouvernementale',
        ]);

        return $user;
    }
}
