<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakePresensiKeluarNullable extends Migration
{
    public function up()
    {
        // Alter table untuk membuat kolom tanggal_keluar dan jam_keluar nullable
        $this->db->disableForeignKeyChecks();
        
        $this->forge->modifyColumn('presensi', [
            'tanggal_keluar' => [
                'type'       => 'DATE',
                'null'       => true,
            ],
            'jam_keluar' => [
                'type'       => 'TIME',
                'null'       => true,
            ],
        ]);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        
        $this->forge->modifyColumn('presensi', [
            'tanggal_keluar' => [
                'type'       => 'DATE',
                'null'       => false,
            ],
            'jam_keluar' => [
                'type'       => 'TIME',
                'null'       => false,
            ],
        ]);

        $this->db->enableForeignKeyChecks();
    }
}
