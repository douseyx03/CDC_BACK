<?php

namespace App\Services\Agent;

use App\Models\Agent;
use App\Models\User;
use App\Notifications\AgentCredentialsNotification;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class AgentService
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Agent
    {
        return $this->db->transaction(function () use ($data) {
            $password = Str::random(12);

            /** @var User $user */
            $user = User::query()->create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'telephone' => $data['telephone'],
                'password' => Hash::make($password),
            ]);

            $agent = Agent::query()->create([
                'division' => $data['division'],
                'matricule' => $data['matricule'],
                'poste' => $data['poste'],
                'user_id' => $user->id,
            ]);

            if (!empty($data['roles'])) {
                $this->syncUserRoles($user, $data['roles']);
            }

            $user->notify(new AgentCredentialsNotification($password));

            return $agent->load(['user.roles']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Agent $agent, array $data): Agent
    {
        return $this->db->transaction(function () use ($agent, $data) {
            $user = $agent->user;

            $userData = Arr::only($data, ['nom', 'prenom', 'email', 'telephone']);
            if (!empty($userData)) {
                $user->fill($userData);
                $user->save();
            }

            $agentData = Arr::only($data, ['division', 'matricule', 'poste']);
            if (!empty($agentData)) {
                $agent->fill($agentData);
                $agent->save();
            }

            if (array_key_exists('roles', $data)) {
                $this->syncUserRoles($user, $data['roles'] ?? []);
            }

            return $agent->load(['user.roles']);
        });
    }

    public function delete(Agent $agent): void
    {
        $this->db->transaction(function () use ($agent) {
            $user = $agent->user;
            $user?->syncRoles([]);
            $agent->delete();
        });
    }

    private function syncUserRoles(User $user, array $roles): void
    {
        try {
            $user->syncRoles($roles);
        } catch (RoleDoesNotExist $exception) {
            throw ValidationException::withMessages([
                'roles' => [$exception->getMessage()],
            ]);
        }
    }
}
