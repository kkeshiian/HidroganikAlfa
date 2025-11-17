<?php

namespace App\Models;

use CodeIgniter\Model;

class CalibrationModel extends Model
{
    protected $table = 'calibration_settings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kebun',
        'device',
        'ph_slope',
        'ph_intercept',
        'tds_offset',
        'tds_multiplier',
        'suhu_offset',
        'suhu_multiplier',
        'active',
        'updated_at'
    ];
    
    protected $useTimestamps = false;
    
    /**
     * Get active calibration settings
     */
    public function getActiveCalibration($kebun, $device = 'A')
    {
        return $this->where([
            'kebun' => $kebun,
            'device' => $device,
            'active' => 1
        ])->first();
    }
    
    /**
     * Apply calibration to pH value using 2-point calibration
     * Formula: pH_calibrated = slope * pH_raw + intercept
     */
    public function calibratePH($rawValue, $settings)
    {
        if (!$settings || $rawValue === null) {
            return $rawValue;
        }
        
        $slope = $settings['ph_slope'] ?? null;
        $intercept = $settings['ph_intercept'] ?? null;
        
        // If no calibration data, return raw value
        if ($slope === null || $intercept === null) {
            return $rawValue;
        }
        
        return round(($slope * $rawValue) + $intercept, 2);
    }
    
    /**
     * Apply calibration to TDS value (multiplier only, no offset)
     */
    public function calibrateTDS($rawValue, $settings)
    {
        if (!$settings || $rawValue === null) {
            return $rawValue;
        }
        
        $multiplier = $settings['tds_multiplier'] ?? 1.0;
        
        return round($rawValue * $multiplier);
    }
    
    /**
     * Apply calibration to Suhu value (offset only)
     */
    public function calibrateSuhu($rawValue, $settings)
    {
        if (!$settings || $rawValue === null) {
            return $rawValue;
        }
        
        $offset = $settings['suhu_offset'] ?? 0;
        
        return round($rawValue + $offset, 2);
    }
}
