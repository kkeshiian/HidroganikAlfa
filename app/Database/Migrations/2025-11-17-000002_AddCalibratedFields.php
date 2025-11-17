<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCalibratedFields extends Migration
{
    public function up()
    {
        $fields = [
            'ph_calibrated' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'pH setelah kalibrasi',
                'after'      => 'ph'
            ],
            'tds_calibrated' => [
                'type'       => 'INT',
                'null'       => true,
                'comment'    => 'TDS setelah kalibrasi',
                'after'      => 'tds'
            ],
            'suhu_air_calibrated' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => true,
                'comment'    => 'Suhu setelah kalibrasi',
                'after'      => 'suhu_air'
            ],
        ];
        
        $this->forge->addColumn('telemetry_logs', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('telemetry_logs', ['ph_calibrated', 'tds_calibrated', 'suhu_air_calibrated']);
    }
}
