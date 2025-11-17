<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'setting_key' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('setting_key', true);
        $this->forge->createTable('system_settings');

        // Insert default save_interval setting (0 = save all data)
        $this->db->table('system_settings')->insert([
            'setting_key' => 'save_interval',
            'setting_value' => '0',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Initialize last save time for both kebun
        $this->db->table('system_settings')->insertBatch([
            [
                'setting_key' => 'last_save_time_kebun_a',
                'setting_value' => '0',
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'setting_key' => 'last_save_time_kebun_b',
                'setting_value' => '0',
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('system_settings');
    }
}
