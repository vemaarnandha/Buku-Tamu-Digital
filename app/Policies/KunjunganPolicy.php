<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Kunjungan;
use App\Models\User;

class KunjunganPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function view(User $user, Kunjungan $kunjungan): bool
    {
        if (in_array($user->role, [Role::Resepsionis, Role::Admin], true)) {
            return true;
        }

        if ($user->role === Role::Pegawai && $user->pegawai?->id === $kunjungan->pegawai_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function update(User $user, Kunjungan $kunjungan): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function delete(User $user, Kunjungan $kunjungan): bool
    {
        return $user->role === Role::Admin;
    }

    public function klasifikasi(User $user, Kunjungan $kunjungan): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function assignPegawai(User $user, Kunjungan $kunjungan): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function changeStatus(User $user, Kunjungan $kunjungan): bool
    {
        if (in_array($user->role, [Role::Resepsionis, Role::Admin], true)) {
            return true;
        }

        if ($user->role === Role::Pegawai && $user->pegawai?->id === $kunjungan->pegawai_id) {
            return true;
        }

        return false;
    }

    public function markTidakTerlayani(User $user, Kunjungan $kunjungan): bool
    {
        return in_array($user->role, [Role::Resepsionis, Role::Admin], true);
    }

    public function correctClassification(User $user, Kunjungan $kunjungan): bool
    {
        return $this->klasifikasi($user, $kunjungan);
    }

    public function reassignPegawai(User $user, Kunjungan $kunjungan): bool
    {
        return $this->assignPegawai($user, $kunjungan);
    }

    public function updateStatus(User $user, Kunjungan $kunjungan): bool
    {
        return $this->changeStatus($user, $kunjungan);
    }
}
