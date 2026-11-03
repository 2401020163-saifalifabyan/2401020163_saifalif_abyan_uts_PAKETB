<?php

namespace App\Support;

class StatusHelper
{
    public static function statusJumlah(int $jumlah): string
    {
        if ($jumlah >= 3) {
            return 'Banyak';
        } else {
            return 'Sedikit';
        }
    }
}
