<?php

namespace App\Requests;

class StoreAttendanceRequest
{
    public static function rules(): array
    {
        return [
            'rfid' => 'required|string|max_length[50]',
        ];
    }

    public static function messages(): array
    {
        return [
            'rfid' => [
                'required' => 'An RFID tag is required to log attendance.',
            ]
        ];
    }
}