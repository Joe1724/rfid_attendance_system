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
        // Get current date and time
        $today = date('Y-m-d');
        $now   = date('Y-m-d H:i:s');

        // Find the student by RFID
        $studentId = $this->findStudentByRfid($rfidValue);
        
        if ($studentId == null) {
            throw new Exception('RFID not registered in the system.');
        }

        // Check if student already has attendance record today
        $existingRecord = $this->getTodayAttendance($studentId, $today);

        // If no record exists, create time in
        if ($existingRecord == null) {
            $newRecordId = $this->createTimeIn($studentId, $today, $now);
            $record = $this->attendanceModel->find($newRecordId);
            
            return [
                'action' => 'Time In',
                'record' => $record
            ];
        }

        // If record exists, update time out
        $this->updateTimeOut($existingRecord['id'], $now);
        $record = $this->attendanceModel->find($existingRecord['id']);
        
        return [
            'action' => 'Time Out',
            'record' => $record
        ];
    }

    private function findStudentByRfid(string $rfidValue)
    {
        $db = \Config\Database::connect();
        
        // Step 1: Find the RFID record
        $rfidTable = $db->table('rfids');
        $rfidRow = $rfidTable->getWhere(['rfid' => $rfidValue])->getRow();

        if ($rfidRow == null) {
            return null;
        }

        $rfidId = $rfidRow->id;

        // Step 2: Find the student ID using the RFID ID
        $linkTable = $db->table('student_rfids');
        $linkRow = $linkTable->getWhere(['rfid_id' => $rfidId])->getRow();

        if ($linkRow == null) {
            return null;
        }

        return $linkRow->student_id;
    }

    private function getTodayAttendance(int $studentId, string $date)
    {
        $db = \Config\Database::connect();
        $attendanceTable = $db->table('attendances');
        
        $record = $attendanceTable->getWhere([
            'student_id' => $studentId,
            'date' => $date
        ])->getRowArray();

        return $record;
    }

    private function createTimeIn(int $studentId, string $date, string $time)
    {
        $data = [
            'student_id' => $studentId,
            'time_in'    => $time,
            'date'       => $date
        ];

        $this->attendanceModel->insert($data);
        $newId = $this->attendanceModel->getInsertID();
        
        return $newId;
    }

    private function updateTimeOut(int $recordId, string $time)
    {
        $data = [
            'time_out' => $time
        ];

        $this->attendanceModel->update($recordId, $data);
    }
}