<?php

namespace App\Services\Ai;

final readonly class HasilKlasifikasi
{
    public function __construct(
        public string $unitKode,
        public string $kategori,
        public string $ringkasan,
        public float $confidence,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): ?self
    {
        $unitKode = $data['unit_kode'] ?? null;
        $kategori = $data['kategori'] ?? null;
        $ringkasan = $data['ringkasan'] ?? null;
        $confidence = $data['confidence'] ?? null;

        if (! is_string($unitKode) || trim($unitKode) === '') {
            return null;
        }

        if (! is_string($kategori) || trim($kategori) === '') {
            return null;
        }

        if (! is_string($ringkasan) || trim($ringkasan) === '') {
            return null;
        }

        if (! is_numeric($confidence)) {
            return null;
        }

        $confidence = (float) $confidence;

        if ($confidence < 0 || $confidence > 1) {
            return null;
        }

        return new self(
            unitKode: trim($unitKode),
            kategori: trim($kategori),
            ringkasan: trim($ringkasan),
            confidence: $confidence,
        );
    }

    public function memenuhiConfidence(float $minimum): bool
    {
        return $this->confidence >= $minimum;
    }
}
