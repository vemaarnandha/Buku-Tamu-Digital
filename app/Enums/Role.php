<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Resepsionis = 'resepsionis';
    case Pegawai = 'pegawai';
    case Pimpinan = 'pimpinan';
}
