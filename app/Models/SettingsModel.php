<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingsModel extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'setting_key';
    protected $allowedFields = ['setting_key', 'setting_value', 'updated_at'];
    protected $useTimestamps = false;

    /**
     * Get a setting value by key
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        $setting = $this->find($key);
        
        if ($setting) {
            return $setting['setting_value'];
        }
        
        return $default;
    }

    /**
     * Set a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function setSetting(string $key, $value): bool
    {
        $data = [
            'setting_key' => $key,
            'setting_value' => $value,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if setting exists
        $existing = $this->find($key);
        
        if ($existing) {
            // Update existing
            return $this->update($key, $data);
        } else {
            // Insert new
            return $this->insert($data) !== false;
        }
    }

    /**
     * Get the last save timestamp for a specific kebun
     * 
     * @param string $kebun Kebun identifier (a or b)
     * @return int Unix timestamp
     */
    public function getLastSaveTime(string $kebun): int
    {
        $key = "last_save_time_kebun_{$kebun}";
        $timestamp = $this->getSetting($key, 0);
        return (int)$timestamp;
    }

    /**
     * Update the last save timestamp for a specific kebun
     * 
     * @param string $kebun Kebun identifier (a or b)
     * @param int $timestamp Unix timestamp
     * @return bool
     */
    public function updateLastSaveTime(string $kebun, int $timestamp): bool
    {
        $key = "last_save_time_kebun_{$kebun}";
        return $this->setSetting($key, $timestamp);
    }
}
