<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Shifts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'lokasi_presensi_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'nama_shift' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'jam_masuk' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'jam_keluar' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('lokasi_presensi_id', 'lokasi_presensi', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('shifts');
    }

    public function down()
    {
        $this->forge->dropTable('shifts');
    }
}
