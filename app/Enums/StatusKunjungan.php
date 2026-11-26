<?php

namespace App\Enums;

enum StatusKunjungan: string
{
    case Menunggu = 'menunggu';
    case Dilayani = 'dilayani';
    case Selesai = 'selesai';
    case TidakTerlayani = 'tidak_terlayani';
}
