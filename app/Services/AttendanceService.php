<?php

namespace App\Services;

use App\Models\StudentRfidModel;
use App\Models\AttendanceModel;
use Exception;

class AttendanceService
{
    protected $studentRfidModel;
    protected $attendanceModel;

    public function __construct()
    {
        $this->studentRfidModel = new StudentRfidModel();
        $this->attendanceModel  = new AttendanceModel();
    }

    public function processTap(string $rfidValue): array
    {
        $today = date('Y-m-d');
        $now   = date('Y-m-d H:i:s');

        // STEP 1: Identification
        $link = $this->studentRfidModel->select('student_rfids.student_id')
                                ->join('rfids', 'rfids.id = student_rfids.rfid_id')
                                ->where('rfids.rfid', $rfidValue)
                                ->first();

        // Throw an exception instead of formatting a JSON error
        if (!$link) {
            throw new Exception('RFID not registered in the system.');
        }

        $studentId = $link['student_id'];

        // STEP 2: The "Existence" Check
        $record = $this->attendanceModel->where(['student_id' => $studentId, 'date' => $today])->first();

        // STEP 3: Action Decision
        if (!$record) {
            // Time In
            $this->attendanceModel->insert([
                'student_id' => $studentId,
                'time_in'    => $now,
                'date'       => $today
            ]);
            
            // Return the raw action type and the fresh record data
            return [
                'action' => 'Time In',
                'record' => $this->attendanceModel->find($this->attendanceModel->getInsertID())
            ];
        } 
        
        // Time Out
        $this->attendanceModel->update($record['id'], ['time_out' => $now]);
        
        return [
            'action' => 'Time Out',
            'record' => $this->attendanceModel->find($record['id'])
        ];
    }
}