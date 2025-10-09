<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;

class DemandePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->profileType() !== null;
    }

    public function view(User $user, Demande $demande): bool
    {
        return $user->isAdmin() || $demande->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->profileType() !== null;
    }

    public function update(User $user, Demande $demande): bool
    {
        return $user->isAdmin() || $demande->user_id === $user->id;
    }

    public function delete(User $user, Demande $demande): bool
    {
        return $user->isAdmin() || $demande->user_id === $user->id;
    }

    public function restore(User $user, Demande $demande): bool
    {
        return $this->delete($user, $demande);
    }

    public function forceDelete(User $user, Demande $demande): bool
    {
        return $user->isAdmin();
    }
}
