<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_publik')->unique();
            $table->foreignId('tamu_id')->constrained('tamu')->cascadeOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawai')->nullOnDelete();
            $table->text('keperluan');
            $table->string('kategori')->nullable();
            $table->text('ringkasan_ai')->nullable();
            $table->string('status')->default('menunggu');
            $table->string('klasifikasi_status')->default('diproses');
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->text('alasan_tidak_terlayani')->nullable();
            $table->text('catatan')->nullable();
            $table->timestampTz('waktu_datang');
            $table->timestampTz('waktu_dilayani')->nullable();
            $table->timestampTz('waktu_selesai')->nullable();
            $table->timestampTz('notifikasi_terkirim_pada')->nullable();
            $table->timestampsTz();

            $table->index(['status', 'klasifikasi_status']);
            $table->index('waktu_datang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan');
    }
};
