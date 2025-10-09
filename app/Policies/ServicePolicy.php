<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function view(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    public function restore(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->institution !== null;
    }
}
