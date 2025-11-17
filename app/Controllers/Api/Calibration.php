<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\CalibrationModel;

class Calibration extends ResourceController
{
    protected $format = 'json';

    public function getSettings()
    {
        $model = new CalibrationModel();
        $settings = $model->findAll();
        
        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setJSON([
                'status' => 'success',
                'data' => $settings
            ]);
    }
    
    public function updateSettings()
    {
        $model = new CalibrationModel();
        $input = $this->request->getJSON(true);
        
        $id = $input['id'] ?? null;
        
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID required'
            ])->setStatusCode(400);
        }
        
        $updateData = [
            'ph_slope' => $input['ph_slope'] ?? null,
            'ph_intercept' => $input['ph_intercept'] ?? null,
            'tds_offset' => $input['tds_offset'] ?? 0,
            'tds_multiplier' => $input['tds_multiplier'] ?? 1.0,
            'suhu_offset' => $input['suhu_offset'] ?? 0,
            'suhu_multiplier' => $input['suhu_multiplier'] ?? 1.0,
            'active' => $input['active'] ?? 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($model->update($id, $updateData)) {
            return $this->response
                ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->setJSON([
                    'status' => 'success',
                    'message' => 'Kalibrasi berhasil disimpan'
                ]);
        }
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Gagal menyimpan kalibrasi'
        ])->setStatusCode(500);
    }
    
    /**
     * Save pH calibration data (called during 2-point calibration process)
     */
    public function savePhCalibration()
    {
        $model = new CalibrationModel();
        $input = $this->request->getJSON(true);
        
        $id = $input['id'] ?? null;
        $ph401Reading = $input['ph401_reading'] ?? null;
        $ph686Reading = $input['ph686_reading'] ?? null;
        
        if (!$id || $ph401Reading === null || $ph686Reading === null) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Missing required parameters'
            ])->setStatusCode(400);
        }
        
        // Calculate slope and intercept using 2-point calibration
        // y = mx + b
        // pH_actual = slope * pH_raw + intercept
        // Point 1: (ph401Reading, 4.01)
        // Point 2: (ph686Reading, 6.86)
        
        $x1 = floatval($ph401Reading);
        $y1 = 4.01;
        $x2 = floatval($ph686Reading);
        $y2 = 6.86;
        
        if ($x1 == $x2) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Invalid calibration readings (identical values)'
            ])->setStatusCode(400);
        }
        
        $slope = ($y2 - $y1) / ($x2 - $x1);
        $intercept = $y1 - ($slope * $x1);
        
        $updateData = [
            'ph_slope' => $slope,
            'ph_intercept' => $intercept,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($model->update($id, $updateData)) {
            return $this->response
                ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->setJSON([
                    'status' => 'success',
                    'message' => 'Kalibrasi pH berhasil',
                    'data' => [
                        'slope' => round($slope, 6),
                        'intercept' => round($intercept, 6)
                    ]
                ]);
        }
        
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Gagal menyimpan kalibrasi pH'
        ])->setStatusCode(500);
    }
}
