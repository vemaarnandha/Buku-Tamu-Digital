<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jam_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->boolean('aktif')->default(true);
            $table->timestampsTz();
        });

        Schema::table('jam_layanan', function (Blueprint $table) {
            $table->index(['unit_id', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jam_layanan');
    }
};
