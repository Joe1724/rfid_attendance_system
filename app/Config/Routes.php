<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// --------------------------------------------------------------------
// API V1 ROUTE GROUP
// --------------------------------------------------------------------
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api\v1'], static function ($routes) {
    
    $routes->post('students', 'StudentController::register');

    
    $routes->post('attendance', 'AttendanceController::store');

});