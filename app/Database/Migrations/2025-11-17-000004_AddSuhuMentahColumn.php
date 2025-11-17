<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuhuMentahColumn extends Migration
{
    public function up()
    {
        $fields = [
            'suhu_mentah' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
                'after' => 'suhu_air',
            ],
        ];
        
        $this->forge->addColumn('telemetry_logs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('telemetry_logs', 'suhu_mentah');
    }
}
