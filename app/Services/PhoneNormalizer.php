<?php

namespace App\Services;

class PhoneNormalizer
{
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === null || $digits === '') {
            throw new \InvalidArgumentException('Nomor HP tidak valid.');
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        } elseif (str_starts_with($digits, '+62')) {
            // already stripped; fall through
        }

        if (! str_starts_with($digits, '62')) {
            throw new \InvalidArgumentException('Nomor HP harus diawali 0, 62, atau +62.');
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            throw new \InvalidArgumentException('Panjang nomor HP tidak valid.');
        }

        return $digits;
    }
}
