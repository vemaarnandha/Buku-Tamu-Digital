<?php

namespace App\Enums;

enum KlasifikasiStatus: string
{
    case Diproses = 'diproses';
    case Otomatis = 'otomatis';
    case Manual = 'manual';
    case PerluDitinjau = 'perlu_ditinjau';
}
