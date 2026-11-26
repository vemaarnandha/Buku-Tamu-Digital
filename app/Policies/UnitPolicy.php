<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function view(User $user, Unit $model): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function update(User $user, Unit $model): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function delete(User $user, Unit $model): bool
    {
        return $user->role === Role::Admin;
    }
}
