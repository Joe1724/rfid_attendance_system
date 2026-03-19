<?php

namespace App\Services;

use App\Models\StudentModel;
use App\Models\RfidModel;
use Exception;

class StudentService
{
    protected $studentModel;
    protected $rfidModel;
    protected $db;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
        $this->rfidModel    = new RfidModel();
        $this->db           = \Config\Database::connect();
    }

    public function registerStudent(array $data): array
    {
        // Start database transaction
        $this->db->transStart();

        try {
            // Step 1: Create the student record
            $studentId = $this->createStudent($data);
            
            if ($studentId == null) {
                $errors = $this->studentModel->errors();
                $errorMessage = implode(', ', $errors);
                throw new Exception('Failed to create student: ' . $errorMessage);
            }

            // Step 2: Create the RFID record
            $rfidId = $this->createRfid($data['rfid']);
            
            if ($rfidId == null) {
                $errors = $this->rfidModel->errors();
                $errorMessage = implode(', ', $errors);
                throw new Exception('Failed to create RFID: ' . $errorMessage);
            }

            // Step 3: Link student and RFID together
            $linkSuccess = $this->linkStudentToRfid($studentId, $rfidId);
            
            if ($linkSuccess == false) {
                $error = $this->db->error();
                throw new Exception('Failed to link student and RFID: ' . $error['message']);
            }

            // Complete the transaction
            $this->db->transComplete();

            // Check if transaction was successful
            $transactionSuccess = $this->db->transStatus();
            if ($transactionSuccess == false) {
                $error = $this->db->error();
                throw new Exception('Transaction failed: ' . $error['message']);
            }

            // Return the created student data
            $result = [
                'id'    => $studentId,
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'mname' => isset($data['mname']) ? $data['mname'] : null,
                'rfid'  => $data['rfid']
            ];

            return $result;

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->transRollback();
            throw new Exception($e->getMessage());
        }
    }

    private function createStudent(array $data)
    {
        $studentData = [
            'fname' => $data['fname'],
            'lname' => $data['lname'],
            'mname' => isset($data['mname']) ? $data['mname'] : null,
        ];

        $studentId = $this->studentModel->insert($studentData);
        
        return $studentId;
    }

    private function createRfid(string $rfidValue)
    {
        $rfidData = [
            'rfid' => $rfidValue
        ];

        $rfidId = $this->rfidModel->insert($rfidData);
        
        return $rfidId;
    }

    private function linkStudentToRfid(int $studentId, int $rfidId)
    {
        $linkData = [
            'student_id' => $studentId,
            'rfid_id'    => $rfidId
        ];

        $success = $this->db->table('student_rfids')->insert($linkData);
        
        return $success;
    }
}