<?php

namespace App\Models;

use CodeIgniter\Model;

class TelemetryModel extends Model
{
    protected $table = 'telemetry_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'kebun', 'device', 'ph', 'tds', 'tds_mentah', 'suhu_air', 'suhu_mentah', 'ph_calibrated', 'tds_calibrated', 'suhu_air_calibrated', 'cal_ph_asam', 'cal_ph_netral', 'cal_tds_k', 'date', 'time', 'timestamp_ms', 'created_at'
    ];
    protected $useTimestamps = false;
}
