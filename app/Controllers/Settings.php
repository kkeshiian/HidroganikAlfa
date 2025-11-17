<?php

namespace App\Controllers;

use App\Models\SettingsModel;

class Settings extends BaseController
{
    protected $settingsModel;

    public function __construct()
    {
        $this->settingsModel = new SettingsModel();
    }

    /**
     * Display settings page
     */
    public function index()
    {
        // Get current settings
        $saveInterval = $this->settingsModel->getSetting('save_interval', 0);
        
        $data = [
            'title' => 'Pengaturan Sistem',
            'save_interval' => $saveInterval,
        ];

        return view('pages/settings', $data);
    }

    /**
     * Update save interval setting
     */
    public function updateSaveInterval()
    {
        // Validate input
        $rules = [
            'save_interval' => 'required|integer|in_list[0,60,300,600,900,1800,3600]'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Interval tidak valid. Pilih salah satu opsi yang tersedia.',
                'errors' => $this->validator->getErrors()
            ]);
        }

        $interval = $this->request->getPost('save_interval');

        // Update setting
        $updated = $this->settingsModel->setSetting('save_interval', $interval);

        if ($updated) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Pengaturan interval penyimpanan berhasil diperbarui.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal memperbarui pengaturan.'
            ]);
        }
    }

    /**
     * Get current save interval (API)
     */
    public function getSaveInterval()
    {
        $interval = $this->settingsModel->getSetting('save_interval', 0);
        
        return $this->response->setJSON([
            'success' => true,
            'save_interval' => (int)$interval
        ]);
    }
}
