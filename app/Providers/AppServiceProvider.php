<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\Faq;
use App\Models\JamLayanan;
use App\Models\Kunjungan;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Models\User;
use App\Policies\FaqPolicy;
use App\Policies\JamLayananPolicy;
use App\Policies\KunjunganPolicy;
use App\Policies\PegawaiPolicy;
use App\Policies\UnitPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Gate::define('view-rekap', fn (User $user) => in_array($user->role, [Role::Resepsionis, Role::Admin, Role::Pimpinan], true));
        Gate::define('view-ringkasan', fn (User $user) => in_array($user->role, [Role::Admin, Role::Pimpinan], true));

        Gate::policy(Kunjungan::class, KunjunganPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Pegawai::class, PegawaiPolicy::class);
        Gate::policy(JamLayanan::class, JamLayananPolicy::class);
        Gate::policy(Faq::class, FaqPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
