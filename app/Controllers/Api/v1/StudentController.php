<?php

namespace App\Controllers\Api\v1;

use App\Controllers\BaseController;
use App\Requests\StoreStudentRequest;
use App\Services\StudentService;
use App\Resources\StudentResource;
use Exception;

class StudentController extends BaseController
{
    public function register()
    {
        // 1. Capture the raw JSON payload as an array
        // The 'true' parameter ensures it returns an associative array instead of an object
        $data = $this->request->getJSON(true);

        // Fallback: If someone sends Form-Data by mistake instead of JSON
        if (empty($data)) {
            $data = $this->request->getPost();
        }

        // 2. REQUEST LAYER: Validate Input
        $validation = \Config\Services::validation();
        $validation->setRules(StoreStudentRequest::rules(), StoreStudentRequest::messages());

        // Pass our $data array directly into run() instead of using withRequest()
        if (!$validation->run($data)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ]);
        }

        try {
            // 3. SERVICE LAYER: Execute Business Logic using $data
            $service = new StudentService();
            $rawResult = $service->registerStudent($data);

            // 4. RESOURCE LAYER: Format Output
            $formattedResponse = StudentResource::make($rawResult);

            // 5. Return final JSON (201 Created)
            return $this->response->setStatusCode(201)->setJSON($formattedResponse);

        } catch (Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}