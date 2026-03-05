<?php

namespace App\Requests;

class StoreStudentRequest
{
    public static function rules(): array
    {
        return [
            'fname' => 'required|min_length[2]|max_length[100]',
            'lname' => 'required|min_length[2]|max_length[100]',
            'mname' => 'permit_empty|max_length[100]',
            'rfid'  => 'required|max_length[50]|is_unique[rfids.rfid]',
        ];
    }

    public static function messages(): array
    {
        return [
            'rfid' => [
                'required'  => 'An RFID tag must be scanned.',
                'is_unique' => 'This RFID tag is already registered to another student.'
            ]
        ];
    }
}