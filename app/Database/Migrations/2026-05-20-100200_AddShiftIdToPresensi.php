<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddShiftIdToPresensi extends Migration
{
    public function up()
    {
        $fields = [
            'shift_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ];

        $this->forge->addColumn('presensi', $fields);
        $this->forge->addForeignKey('shift_id', 'shifts', 'id', 'SET NULL', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropColumn('presensi', 'shift_id');
    }
}
