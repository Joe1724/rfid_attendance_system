<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAttendanceTables extends Migration
{
    public function up()
    {
        // ----------------------------------------------------------------
        // 1. STUDENTS TABLE
        // ----------------------------------------------------------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'fname'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'lname'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'mname'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true], 
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('students');

        // ----------------------------------------------------------------
        // 2. RFIDS TABLE
        // ----------------------------------------------------------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rfid'       => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('rfids');

        // ----------------------------------------------------------------
        // 3. STUDENT_RFIDS TABLE (Pivot Table)
        // ----------------------------------------------------------------
        $this->forge->addField([
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rfid_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);
        // Foreign Keys
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rfid_id', 'rfids', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('student_rfids');

        // ----------------------------------------------------------------
        // 4. ATTENDANCE TABLE
        // ----------------------------------------------------------------
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'student_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'     => ['type' => 'ENUM', 'constraint' => ['in', 'out']],
            'created_at' => ['type' => 'DATETIME', 'null' => true], // Added this so you know exactly WHEN they tapped in/out
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('student_id', 'students', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('attendance');
    }

    public function down()
    {
        // Drop tables in reverse order to avoid foreign key constraint errors
        $this->forge->dropTable('attendance', true);
        $this->forge->dropTable('student_rfids', true);
        $this->forge->dropTable('rfids', true);
        $this->forge->dropTable('students', true);
    }
}