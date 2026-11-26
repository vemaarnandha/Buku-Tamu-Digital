<?php

namespace App\Mail;

use App\Models\Kunjungan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiKunjungan extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Kunjungan $kunjungan) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tamu menunggu di '.($this->kunjungan->unit?->lokasi_ruang ?? 'ruang tamu'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.notifikasi-kunjungan',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
