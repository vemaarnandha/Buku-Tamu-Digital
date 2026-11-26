<?php

namespace Tests\Feature\Guest;

use App\Enums\StatusKunjungan;
use App\Jobs\KlasifikasiKunjungan;
use App\Models\Kunjungan;
use App\Models\Tamu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class KunjunganFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    public function test_form_page_renders(): void
    {
        $this->get('/')->assertStatus(200)->assertInertia(fn ($page) => $page->component('Guest/Index'));
    }

    public function test_lookup_existing_no_hp(): void
    {
        $tamu = Tamu::factory()->create(['no_hp' => '628123456789']);
        Kunjungan::factory()->create(['tamu_id' => $tamu->id, 'status' => StatusKunjungan::Selesai]);

        $response = $this->get('/kunjungan/create?no_hp=08123456789');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->component('Guest/Index')
                ->where('tamu.no_hp', '628123456789')
                ->where('kunjunganSelesai', 1)
            );
    }

    public function test_store_new_tamu_creates_kunjungan_and_redirects(): void
    {
        $response = $this->post('/kunjungan', [
            'no_hp' => '08123456789',
            'nama' => 'Budi',
            'kategori' => 'instansi',
            'instansi' => 'Dinas X',
            'alamat' => 'Jl. Test',
            'keperluan' => 'Konsultasi data',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tamu', ['no_hp' => '628123456789', 'nama' => 'Budi']);
        $kunjungan = Kunjungan::first();
        $this->assertNotNull($kunjungan);
        $this->assertSame('menunggu', $kunjungan->status->value);
        $this->assertSame('diproses', $kunjungan->klasifikasi_status->value);
        $this->assertNotSame((string) $kunjungan->id, $kunjungan->kode_publik);
        Queue::assertPushed(KlasifikasiKunjungan::class, fn ($job) => $job->kunjungan->is($kunjungan));
    }

    public function test_store_existing_tamu_updates_master(): void
    {
        $tamu = Tamu::factory()->create(['no_hp' => '628123456789', 'nama' => 'Lama']);

        $this->post('/kunjungan', [
            'no_hp' => '628123456789',
            'nama' => 'Baru',
            'kategori' => 'perorangan',
            'keperluan' => 'Lain',
        ])->assertRedirect();

        $this->assertDatabaseHas('tamu', ['id' => $tamu->id, 'nama' => 'Baru']);
        $this->assertSame(1, Kunjungan::count());
    }

    public function test_status_page_accessible_by_kode_publik(): void
    {
        $kunjungan = Kunjungan::factory()->create(['kode_publik' => 'abc123xyz']);

        $this->get('/kunjungan/abc123xyz/status')
            ->assertStatus(200)
            ->assertInertia(fn ($page) => $page->component('Guest/Status'));
    }

    public function test_status_page_not_accessible_by_id(): void
    {
        $kunjungan = Kunjungan::factory()->create();

        $this->get('/kunjungan/'.$kunjungan->id.'/status')
            ->assertStatus(404);
    }

    public function test_keperluan_required(): void
    {
        $this->post('/kunjungan', [
            'no_hp' => '08123456789',
            'nama' => 'Budi',
            'kategori' => 'perorangan',
            'keperluan' => '',
        ])->assertSessionHasErrors('keperluan');
    }
}
