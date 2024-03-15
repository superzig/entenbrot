<?php

$students = json_decode(file_get_contents('results/updated_students4.json'), true);


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
}
