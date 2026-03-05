<?php

namespace App\Resources;

class StudentResource
{
    public static function make(array $studentData): array
    {
        // Format the full name cleanly, handling the optional middle name
        $fullName = $studentData['fname'];
        if (!empty($studentData['mname'])) {
            $fullName .= ' ' . substr($studentData['mname'], 0, 1) . '.'; // e.g., "John D. Doe"
        }
        $fullName .= ' ' . $studentData['lname'];

        return [
            'status'  => 'success',
            'message' => 'Student registered successfully.',
            'data'    => [
                'student_id'   => (int) $studentData['id'],
                'full_name'    => $fullName,
                'first_name'   => $studentData['fname'],
                'last_name'    => $studentData['lname'],
                'rfid_tag'     => $studentData['rfid']
            ]
        ];
    }
}