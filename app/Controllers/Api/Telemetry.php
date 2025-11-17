<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\TelemetryModel;
use App\Models\CalibrationModel;
use App\Models\SettingsModel;

class Telemetry extends BaseController
{
    /**
     * Ingest telemetry JSON from MQTT bridge.
     * Security: require X-INGEST-TOKEN header matching env('INGEST_TOKEN')
     * Body JSON example:
     * {
     *   "kebun": "kebun-a", // or kebun-b
     *   "ph": 6.5, "tds": 850, "suhu": 25.2,
     *   "cal_ph_asam": 4.0100, "cal_ph_netral": 6.8600, "cal_tds_k": 0.5000,
     *   "timestamp": 1730325600000 // ms since epoch (optional)
     * }
     */
    public function ingest()
    {
        $tokenHeader = $this->request->getHeaderLine('X-INGEST-TOKEN');
        $expected = env('INGEST_TOKEN');
        if (!$expected || $tokenHeader !== $expected) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        $payload = $this->request->getJSON(true) ?? [];

        // Determine kebun and device
        $kebun = strtolower(trim($payload['kebun'] ?? ($payload['kebun_id'] ?? '')));
        if (!$kebun && isset($payload['topic'])) {
            // Optional: derive from topic hidroganik/kebun-a/telemetry
            if (preg_match('~hidroganik/(kebun-[^/]+)/telemetry~i', $payload['topic'], $m)) {
                $kebun = strtolower($m[1]);
            }
        }
        if ($kebun !== 'kebun-a' && $kebun !== 'kebun-b') {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Invalid kebun (expected kebun-a or kebun-b)'
            ]);
        }
        $device = ($kebun === 'kebun-b') ? 'B' : 'A';

        // Normalize numeric fields
        $ph  = isset($payload['ph']) ? (float) $payload['ph'] : null;
        $tdsMentah = isset($payload['tds_mentah']) ? (int) $payload['tds_mentah'] : null;
        $suhuMentah = isset($payload['suhu_air']) ? (float) $payload['suhu_air'] : (isset($payload['suhu']) ? (float) $payload['suhu'] : null);
        $calAsam = isset($payload['cal_ph_asam']) ? (float) $payload['cal_ph_asam'] : null;
        $calNetral = isset($payload['cal_ph_netral']) ? (float) $payload['cal_ph_netral'] : null;
        $calTdsK = isset($payload['cal_tds_k']) ? (float) $payload['cal_tds_k'] : null;

        // Get date and time from MQTT payload
        $date = !empty($payload['date']) ? $payload['date'] : date('Y-m-d');
        $time = !empty($payload['time']) ? $payload['time'] : date('H:i:s');
        
        // Also keep timestamp_ms for compatibility
        $tsMs = null;
        if (!empty($payload['date']) && !empty($payload['time'])) {
            $dt = strtotime($payload['date'] . ' ' . $payload['time']);
            if ($dt !== false) $tsMs = $dt * 1000;
        }
        if (!$tsMs) $tsMs = (int) (time() * 1000);

        // Check save interval setting
        $settingsModel = new SettingsModel();
        $saveInterval = (int) $settingsModel->getSetting('save_interval', 0);
        
        if ($saveInterval > 0) {
            // Check if enough time has passed since last save for this kebun
            $lastSaveTime = $settingsModel->getLastSaveTime($kebun === 'kebun-a' ? 'a' : 'b');
            $currentTime = time();
            
            if (($currentTime - $lastSaveTime) < $saveInterval) {
                // Not enough time has passed, skip saving
                return $this->response->setJSON([
                    'status' => 'skipped',
                    'message' => 'Data skipped due to interval setting',
                    'next_save_in' => $saveInterval - ($currentTime - $lastSaveTime)
                ]);
            }
            
            // Update last save time for this kebun
            $settingsModel->updateLastSaveTime($kebun === 'kebun-a' ? 'a' : 'b', $currentTime);
        }

        // Apply calibration if active
        $calibrationModel = new CalibrationModel();
        $calibration = $calibrationModel->getActiveCalibration($kebun, $device);
        
        $phCalibrated = $ph;
        $tdsCalibrated = $tdsMentah; // Gunakan TDS mentah untuk dikalibrasi
        $suhuCalibrated = $suhuMentah; // Gunakan Suhu mentah untuk dikalibrasi
        
        if ($calibration && $calibration['active']) {
            if ($ph !== null) {
                $phCalibrated = $calibrationModel->calibratePH($ph, $calibration);
            }
            if ($tdsMentah !== null) {
                // Apply kalibrasi ke TDS mentah
                $tdsCalibrated = $calibrationModel->calibrateTDS($tdsMentah, $calibration);
            }
            if ($suhuMentah !== null) {
                // Apply kalibrasi ke Suhu mentah
                $suhuCalibrated = $calibrationModel->calibrateSuhu($suhuMentah, $calibration);
            }
        }

        $data = [
            'kebun' => $kebun,
            'device' => $device,
            'ph' => $phCalibrated,
            'tds' => $tdsCalibrated,  // TDS hasil kalibrasi
            'tds_mentah' => $tdsMentah,  // TDS mentah dari sensor
            'suhu_air' => $suhuCalibrated,  // Suhu hasil kalibrasi
            'suhu_mentah' => $suhuMentah,  // Suhu mentah dari sensor
            'ph_calibrated' => $phCalibrated,
            'tds_calibrated' => $tdsCalibrated,
            'suhu_air_calibrated' => $suhuCalibrated,
            'cal_ph_asam' => $calAsam,
            'cal_ph_netral' => $calNetral,
            'cal_tds_k' => $calTdsK,
            'date' => $date,
            'time' => $time,
            'timestamp_ms' => $tsMs,
            'created_at' => $date . ' ' . $time,
        ];

        try {
            $model = new TelemetryModel();
            $model->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Telemetry ingest failed: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'DB error'
            ]);
        }

        return $this->response->setJSON(['status' => 'ok'])
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0');
    }

    /**
     * Query telemetry logs with filters and pagination.
     * Security: requires login (route under auth filter)
     * GET params:
     * - start: YYYY-MM-DD (optional)
     * - end: YYYY-MM-DD (optional)
     * - device: A|B|all (default all)
     * - page: integer (default 1)
     * - perPage: integer (default 25, max 100)
     * - sort: asc|desc on timestamp_ms (default desc)
     */
    public function query()
    {
        $start = trim((string) $this->request->getGet('start'));
        $end = trim((string) $this->request->getGet('end'));
        $device = strtoupper(trim((string) $this->request->getGet('device')));
        if (!in_array($device, ['A', 'B'], true)) {
            $device = 'ALL';
        }
        $intervalSeconds = (int) ($this->request->getGet('interval') ?? 0);
        if ($intervalSeconds < 0) $intervalSeconds = 0;
        
        $page = (int) ($this->request->getGet('page') ?? 1);
        if ($page < 1) $page = 1;
        $perPage = (int) ($this->request->getGet('perPage') ?? 25);
        if ($perPage < 1) $perPage = 25;
        if ($perPage > 100) $perPage = 100;
        $sort = strtolower((string) ($this->request->getGet('sort') ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $model = new TelemetryModel();
        $builder = $model->builder();

        // Filters
        if ($device !== 'ALL') {
            $builder->where('device', $device);
        }
        // Convert start/end dates to timestamp range if provided
        $startTs = null; $endTs = null;
        if ($start) {
            $dt = strtotime($start . ' 00:00:00');
            if ($dt !== false) $startTs = $dt * 1000;
        }
        if ($end) {
            $dt = strtotime($end . ' 23:59:59');
            if ($dt !== false) $endTs = $dt * 1000;
        }
        if ($startTs !== null) {
            $builder->where('timestamp_ms >=', $startTs);
        }
        if ($endTs !== null) {
            $builder->where('timestamp_ms <=', $endTs);
        }

        // Get WHERE conditions for reuse
        $whereConditions = [];
        if ($device !== 'ALL') {
            $whereConditions['device'] = $device;
        }
        
        // Total count - use fresh builder
        $countBuilder = $model->builder();
        if (!empty($whereConditions)) {
            $countBuilder->where($whereConditions);
        }
        if ($startTs !== null) {
            $countBuilder->where('timestamp_ms >=', $startTs);
        }
        if ($endTs !== null) {
            $countBuilder->where('timestamp_ms <=', $endTs);
        }
        $total = (int) $countBuilder->countAllResults();

        // Stats - use fresh builder
        $statsBuilder = $model->builder();
        if (!empty($whereConditions)) {
            $statsBuilder->where($whereConditions);
        }
        if ($startTs !== null) {
            $statsBuilder->where('timestamp_ms >=', $startTs);
        }
        if ($endTs !== null) {
            $statsBuilder->where('timestamp_ms <=', $endTs);
        }
        $statsBuilder->select('AVG(ph) AS avg_ph, AVG(tds) AS avg_tds, AVG(suhu_air) AS avg_suhu_air');
        $statsRow = $statsBuilder->get()->getRowArray() ?? [];
        $stats = [
            'avg_ph' => isset($statsRow['avg_ph']) ? round((float)$statsRow['avg_ph'], 2) : null,
            'avg_tds' => isset($statsRow['avg_tds']) ? round((float)$statsRow['avg_tds']) : null,
            'avg_suhu_air' => isset($statsRow['avg_suhu_air']) ? round((float)$statsRow['avg_suhu_air'], 2) : null,
        ];

        // Data query
        $offset = ($page - 1) * $perPage;
        $builder->select('id, kebun, device, ph, tds, suhu_air, cal_ph_asam, cal_ph_netral, cal_tds_k, date, time, timestamp_ms, created_at');
        $builder->orderBy('timestamp_ms', $sort);
        
        // If interval sampling is enabled, fetch more data and filter client-side
        if ($intervalSeconds > 0) {
            // Fetch larger dataset for sampling (up to 10000 records)
            $builder->limit(10000, 0);
            $allRows = $builder->get()->getResultArray();
            
            // Apply interval sampling
            $sampledRows = $this->applySampling($allRows, $intervalSeconds);
            
            // Update total to reflect sampled count
            $total = count($sampledRows);
            
            // Paginate sampled results
            $rows = array_slice($sampledRows, $offset, $perPage);
        } else {
            // Normal pagination without sampling
            $builder->limit($perPage, $offset);
            $rows = $builder->get()->getResultArray();
        }

        // Format output
        $items = array_map(static function(array $r){
            return [
                'id' => (int)$r['id'],
                'kebun' => $r['kebun'],
                'device' => $r['device'],
                'ph' => is_null($r['ph']) ? null : (float)$r['ph'],
                'tds' => is_null($r['tds']) ? null : (int)$r['tds'],
                'suhu_air' => is_null($r['suhu_air']) ? null : (float)$r['suhu_air'],
                'cal_ph_asam' => is_null($r['cal_ph_asam']) ? null : (float)$r['cal_ph_asam'],
                'cal_ph_netral' => is_null($r['cal_ph_netral']) ? null : (float)$r['cal_ph_netral'],
                'cal_tds_k' => is_null($r['cal_tds_k']) ? null : (float)$r['cal_tds_k'],
                'date' => $r['date'],
                'time' => $r['time'],
                'timestamp_ms' => is_null($r['timestamp_ms']) ? null : (int)$r['timestamp_ms'],
                'created_at' => $r['created_at'],
            ];
        }, $rows);

        return $this->response->setJSON([
            'status' => 'ok',
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'items' => $items,
            'stats' => $stats,
            'sort' => $sort,
            'device' => $device,
            'interval' => $intervalSeconds > 0 ? $intervalSeconds : null,
            'start' => $start ?: null,
            'end' => $end ?: null,
        ])
        ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', '0');
    }
    
    /**
     * Apply interval sampling to data array
     * Only keep records that are at least $intervalSeconds apart
     * 
     * @param array $rows Data array sorted by timestamp
     * @param int $intervalSeconds Minimum seconds between records
     * @return array Filtered array
     */
    private function applySampling(array $rows, int $intervalSeconds): array
    {
        if (empty($rows) || $intervalSeconds <= 0) {
            return $rows;
        }
        
        $sampled = [];
        $lastTimestamp = null;
        
        foreach ($rows as $row) {
            $currentTimestamp = isset($row['timestamp_ms']) ? (int)$row['timestamp_ms'] : null;
            
            if ($currentTimestamp === null) {
                continue;
            }
            
            // Keep first record or if enough time has passed
            if ($lastTimestamp === null || 
                abs($currentTimestamp - $lastTimestamp) >= ($intervalSeconds * 1000)) {
                $sampled[] = $row;
                $lastTimestamp = $currentTimestamp;
            }
        }
        
        return $sampled;
    }

    /**
     * Get latest sensor reading for each kebun
     * Returns the most recent data for kebun-a and kebun-b
     * Can filter by specific kebun using ?kebun=a or ?kebun=b query parameter
     */
    public function latest()
    {
        $model = new TelemetryModel();
        
        // Check if specific kebun is requested
        $requestedKebun = $this->request->getGet('kebun');
        
        if ($requestedKebun) {
            // Return data for specific kebun
            $kebunId = strtolower($requestedKebun) === 'a' ? 'kebun-a' : 'kebun-b';
            $data = $model->where('kebun', $kebunId)
                ->orderBy('timestamp_ms', 'DESC')
                ->first();
            
            if ($data) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'data' => [
                        'kebun' => $data['kebun'],
                        'device' => $data['device'],
                        'suhu_air' => $data['suhu_air'] ? (float)$data['suhu_air'] : null,
                        'suhu_mentah' => isset($data['suhu_mentah']) && $data['suhu_mentah'] ? (float)$data['suhu_mentah'] : null,
                        'ph' => $data['ph'] ? (float)$data['ph'] : null,
                        'tds' => $data['tds'] ? (int)$data['tds'] : null,
                        'tds_mentah' => isset($data['tds_mentah']) && $data['tds_mentah'] ? (int)$data['tds_mentah'] : null,
                        'cal_ph_asam' => $data['cal_ph_asam'] ? (float)$data['cal_ph_asam'] : null,
                        'cal_ph_netral' => $data['cal_ph_netral'] ? (float)$data['cal_ph_netral'] : null,
                        'cal_tds_k' => $data['cal_tds_k'] ? (float)$data['cal_tds_k'] : null,
                        'date' => $data['date'],
                        'time' => $data['time'],
                    ]
                ])
                ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->setHeader('Pragma', 'no-cache')
                ->setHeader('Expires', '0');
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'No data found for kebun ' . $requestedKebun
                ]);
            }
        }
        
        // Return data for all kebun
        // Get latest for kebun-a
        $kebunA = $model->where('kebun', 'kebun-a')
            ->orderBy('timestamp_ms', 'DESC')
            ->first();
        
        // Get latest for kebun-b
        $kebunB = $model->where('kebun', 'kebun-b')
            ->orderBy('timestamp_ms', 'DESC')
            ->first();
        
        $data = [];
        if ($kebunA) {
            $data[] = [
                'kebun' => $kebunA['kebun'],
                'device' => $kebunA['device'],
                'suhu_air' => $kebunA['suhu_air'] ? (float)$kebunA['suhu_air'] : null,
                'suhu_mentah' => isset($kebunA['suhu_mentah']) && $kebunA['suhu_mentah'] ? (float)$kebunA['suhu_mentah'] : null,
                'ph' => $kebunA['ph'] ? (float)$kebunA['ph'] : null,
                'tds' => $kebunA['tds'] ? (int)$kebunA['tds'] : null,
                'tds_mentah' => isset($kebunA['tds_mentah']) && $kebunA['tds_mentah'] ? (int)$kebunA['tds_mentah'] : null,
                'date' => $kebunA['date'],
                'time' => $kebunA['time'],
            ];
        }
        
        if ($kebunB) {
            $data[] = [
                'kebun' => $kebunB['kebun'],
                'device' => $kebunB['device'],
                'suhu_air' => $kebunB['suhu_air'] ? (float)$kebunB['suhu_air'] : null,
                'suhu_mentah' => isset($kebunB['suhu_mentah']) && $kebunB['suhu_mentah'] ? (float)$kebunB['suhu_mentah'] : null,
                'ph' => $kebunB['ph'] ? (float)$kebunB['ph'] : null,
                'tds' => $kebunB['tds'] ? (int)$kebunB['tds'] : null,
                'tds_mentah' => isset($kebunB['tds_mentah']) && $kebunB['tds_mentah'] ? (int)$kebunB['tds_mentah'] : null,
                'date' => $kebunB['date'],
                'time' => $kebunB['time'],
            ];
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ])
        ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->setHeader('Pragma', 'no-cache')
        ->setHeader('Expires', '0');
    }
}
