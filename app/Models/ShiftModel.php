<?php

namespace App\Models;

use CodeIgniter\Model;

class ShiftModel extends Model
{
    protected $table         = 'shifts';
    protected $allowedFields = [
        'lokasi_presensi_id',
        'nama_shift',
        'jam_masuk',
        'jam_keluar',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
}
