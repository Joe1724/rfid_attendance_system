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
        $this->db->transStart();

        try {
            // STEP 1: Insert into `students`
            // CI4's insert() method conveniently returns the inserted ID on success!
            $studentId = $this->studentModel->insert([
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'mname' => $data['mname'] ?? null,
            ]);

            if (!$studentId) {
                // If it fails, grab the exact validation/model error
                throw new Exception('Student Insert Error: ' . implode(', ', $this->studentModel->errors()));
            }

            // STEP 2: Insert into `rfids`
            $rfidId = $this->rfidModel->insert([
                'rfid' => $data['rfid']
            ]);

            if (!$rfidId) {
                throw new Exception('RFID Insert Error: ' . implode(', ', $this->rfidModel->errors()));
            }

            // STEP 3: Link them in the `student_rfids` pivot table
            // FIX: We use the Query Builder directly here to bypass CI4's primary key requirement for Models
            $pivotInsert = $this->db->table('student_rfids')->insert([
                'student_id' => $studentId,
                'rfid_id'    => $rfidId
            ]);

            if (!$pivotInsert) {
                throw new Exception('Pivot Table Error: ' . $this->db->error()['message']);
            }

            // Complete the Transaction
            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new Exception('Transaction Status Failed: ' . $this->db->error()['message']);
            }

            return [
                'id'    => $studentId,
                'fname' => $data['fname'],
                'lname' => $data['lname'],
                'mname' => $data['mname'] ?? null,
                'rfid'  => $data['rfid']
            ];

        } catch (Exception $e) {
            // Rollback and pass the SPECIFIC error message up to the Controller
            $this->db->transRollback();
            throw new Exception($e->getMessage());
        }
    }
}