<?php

namespace App\Controllers;

class Calibration extends BaseController
{
    public function index()
    {
        return view('pages/calibration', [
            'title' => 'Kalibrasi Sensor'
        ]);
    }
}
