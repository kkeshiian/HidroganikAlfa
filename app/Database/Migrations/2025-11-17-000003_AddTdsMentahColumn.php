<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTdsMentahColumn extends Migration
{
    public function up()
    {
        $this->forge->addColumn('telemetry_logs', [
            'tds_mentah' => [
                'type' => 'INT',
                'null' => true,
                'after' => 'tds'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('telemetry_logs', 'tds_mentah');
    }
}
