<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Pegawai;
use App\Models\User;

class PegawaiPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function view(User $user, Pegawai $model): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function update(User $user, Pegawai $model): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function delete(User $user, Pegawai $model): bool
    {
        return $user->role === Role::Admin;
    }
}
