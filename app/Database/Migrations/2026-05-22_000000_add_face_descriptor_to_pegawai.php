<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFaceDescriptorToPegawai extends Migration
{
    public function up()
    {
        $fields = [
            'face_descriptor' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('pegawai', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pegawai', 'face_descriptor');
    }
}
