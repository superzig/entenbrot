<?php

$students = json_decode(file_get_contents('updated_students2.json'), true);


$assignedRooms = [];

foreach ($students as $student) {

    foreach ($student['assignedRoom'] as $event => $room) {
        if (!isset($assignedRooms[$room]['count'])) {
            $assignedRooms[$room]['count'] = 0;
        }
        $assignedRooms[$room]['count'] += 1;

        $assignedRooms[$room][] = [
            'firstname' => $student['firstname'],
            'lastname' => $student['lastname'],
        ];
    }

}

file_put_contents('assignedRooms.json', json_encode($assignedRooms, JSON_PRETTY_PRINT));
