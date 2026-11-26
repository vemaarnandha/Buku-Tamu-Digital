<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('no_wa');
            $table->string('email')->nullable();
            $table->boolean('aktif')->default(true);
            $table->boolean('bisa_menerima_tamu')->default(true);
            $table->timestampTz('terakhir_ditugaskan_pada')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
