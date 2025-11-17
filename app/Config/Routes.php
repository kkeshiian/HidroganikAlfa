<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rute untuk Login & Logout (Publik, tidak perlu login)
$routes->get('/login', 'AuthController::index');
$routes->post('/login', 'AuthController::loginProcess');
$routes->get('/logout', 'AuthController::logout');

// API ingest route (no login; protected by token header)
$routes->post('api/telemetry/ingest', 'Api\Telemetry::ingest');

// Grup rute yang memerlukan login (SEMENTARA DISABLE AUTH FILTER)
$routes->group('', static function ($routes) {
    $routes->get('/', 'Monitoring::index');
    $routes->get('kalibrasi', 'Monitoring::kalibrasi');
    $routes->get('log', 'Monitoring::log');
    $routes->get('calibration', 'Calibration::index');
    $routes->get('settings', 'Settings::index');
    $routes->post('settings/update-save-interval', 'Settings::updateSaveInterval');
    
    // API query route (requires login)
    $routes->get('api/telemetry', 'Api\\Telemetry::query');
    $routes->get('api/telemetry/latest', 'Api\\Telemetry::latest');
    $routes->get('api/calibration/settings', 'Api\\Calibration::getSettings');
    $routes->post('api/calibration/update', 'Api\\Calibration::updateSettings');
    $routes->post('api/calibration/save-ph', 'Api\\Calibration::savePhCalibration');

    $routes->group('users', ['filter' => 'admin'], static function ($routes) {
        $routes->get('new', 'User::new');
        $routes->post('create', 'User::create');
    });
});