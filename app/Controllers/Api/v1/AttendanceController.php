<?php

namespace App\Controllers\Api\v1;

use App\Controllers\BaseController;
use App\Requests\StoreAttendanceRequest;
use App\Services\AttendanceService;
use App\Resources\AttendanceResource;
use Exception;

class AttendanceController extends BaseController
{
    public function store()
    {
        // 1. REQUEST LAYER: Validate Input
        $validation = \Config\Services::validation();
        $validation->setRules(StoreAttendanceRequest::rules(), StoreAttendanceRequest::messages());

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        $rfidValue = $this->request->getPost('rfid');

        try {
            // 2. SERVICE LAYER: Execute Business Logic
            $service = new AttendanceService();
            $rawResult = $service->processTap($rfidValue);

            // 3. RESOURCE LAYER: Format Output
            $formattedResponse = AttendanceResource::make($rawResult);

            // 4. Return final JSON
            return $this->response->setStatusCode(200)->setJSON($formattedResponse);

        } catch (Exception $e) {
            // Catch the exception thrown by the Service if the RFID isn't found
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}