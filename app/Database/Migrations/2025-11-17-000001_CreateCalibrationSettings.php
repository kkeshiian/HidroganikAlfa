<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalibrationSettings extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kebun' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'device' => [
                'type'       => 'CHAR',
                'constraint' => '1',
                'default'    => 'A',
            ],
            'ph_slope' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'null'       => true,
                'comment'    => 'pH slope from 2-point calibration',
            ],
            'ph_intercept' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,6',
                'null'       => true,
                'comment'    => 'pH intercept from 2-point calibration',
            ],
            'tds_offset' => [
                'type'    => 'INT',
                'default' => 0,
                'comment' => 'TDS offset value (added to result)',
            ],
            'tds_multiplier' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'default'    => 1.0000,
                'comment'    => 'TDS multiplier (raw * multiplier)',
            ],
            'suhu_offset' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 0.00,
                'comment'    => 'Suhu offset (°C)',
            ],
            'suhu_multiplier' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,4',
                'default'    => 1.0000,
                'comment'    => 'Suhu multiplier',
            ],
            'active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addKey(['kebun', 'device']);
        $this->forge->createTable('calibration_settings');
        
        // Insert default calibration for each kebun
        $data = [
            [
                'kebun' => 'kebun-a',
                'device' => 'A',
                'ph_slope' => null,
                'ph_intercept' => null,
                'tds_offset' => 0,
                'tds_multiplier' => 1.0000,
                'suhu_offset' => 0.00,
                'suhu_multiplier' => 1.0000,
                'active' => 1
            ],
            [
                'kebun' => 'kebun-b',
                'device' => 'B',
                'ph_slope' => null,
                'ph_intercept' => null,
                'tds_offset' => 0,
                'tds_multiplier' => 1.0000,
                'suhu_offset' => 0.00,
                'suhu_multiplier' => 1.0000,
                'active' => 1
            ],
        ];
        
        $this->db->table('calibration_settings')->insertBatch($data);
    }

    public function down()
    {
        $this->forge->dropTable('calibration_settings');
    }
}
