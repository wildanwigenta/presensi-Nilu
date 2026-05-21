<?php

namespace App\Models;

use CodeIgniter\Model;

class PegawaiShiftModel extends Model
{
    protected $table         = 'pegawai_shift';
    protected $allowedFields = [
        'pegawai_id',
        'shift_id',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
}
