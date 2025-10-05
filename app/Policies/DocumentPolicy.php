<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->profileType() !== null;
    }

    public function view(User $user, Document $document): bool
    {
        return $user->isAdmin() || $document->user_id === $user->id;
    }

    public function create(User $user, Demande $demande): bool
    {
        return $user->isAdmin() || $demande->user_id === $user->id;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->isAdmin() || $document->user_id === $user->id;
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->isAdmin() || $document->user_id === $user->id;
    }

    public function restore(User $user, Document $document): bool
    {
        return $this->delete($user, $document);
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return $user->isAdmin();
    }
}
