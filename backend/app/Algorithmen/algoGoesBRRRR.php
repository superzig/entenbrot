<?php

$students = json_decode(file_get_contents('updated_students2.json'), true);


$studentsAssignedRoom = [];

foreach ($students as $student) {
    foreach ($student['assignedRoom'] as $eventNumber => $room) {
        if (!isset($studentsAssignedRoom[$room]['studentsCount'])) {
            $studentsAssignedRoom[$room]['studentsCount'] = 0;
        }
        $studentsAssignedRoom[$room]['studentsCount'] += 1;
        $studentsAssignedRoom[$room][] = [
            'firstname' => $student['firstname'],
            'lastname' => $student['lastname'],
        ];
    }
}

file_put_contents('resultsGoesBrrr.json', json_encode($studentsAssignedRoom, JSON_PRETTY_PRINT));

