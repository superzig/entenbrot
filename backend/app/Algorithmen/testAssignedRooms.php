<?php

$students = json_decode(file_get_contents('results/updated_students4.json'), true);
$rooms = json_decode(file_get_contents('data/rooms.json'), true);

$assignedRooms = [];
foreach ($students as $student) {

    if (count( $student['assignedRoom']) < 5) {
        echo "Student " . $student['firstname'] . " " . $student['lastname'] . " has not been assigned to 5 rooms \n";
    }

    // check if each time slot of the student assignedRooms is unique
    $timeSlots = array_map(function ($room) {
        return substr($room, -1);
    }, $student['assignedRoom']);

    if (count($timeSlots) !== count(array_unique($timeSlots))) {
        echo "Student " . $student['firstname'] . " " . $student['lastname'] . " has duplicate time slots \n";
    }

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

// check if the assignedRooms count is not greater than the "capacity" in the $rooms array based on the "name" key
foreach ($assignedRooms as $room => $students) {
    if (count($students) > $rooms[array_search($room, array_column($rooms, 'name'))]['capacity']) {
        echo "Room " . $room . " has more students than its capacity \n";
    }
}

file_put_contents('results/test_assignedRooms.json', json_encode($assignedRooms, JSON_PRETTY_PRINT));
