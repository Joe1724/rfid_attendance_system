<?php

namespace App\Resources;

class AttendanceResource
{
    public static function make(array $serviceResult): array
    {
        $action = $serviceResult['action'];
        $record = $serviceResult['record'];

        return [
            'status'  => 'success',
            'message' => $action . ' recorded successfully',
            'data'    => [
                'attendance_id' => (int) $record['id'],
                'student_id'    => (int) $record['student_id'],
                'date'          => $record['date'],
                'time_in'       => $record['time_in'] ?? '--:--:--',
                'time_out'      => $record['time_out'] ?? '--:--:--',
                'action_taken'  => $action
            ]
        ];
    }
}