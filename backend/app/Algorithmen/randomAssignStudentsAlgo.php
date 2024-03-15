<?php

$students = json_decode(file_get_contents('results/updated_students3.json'), true);
$roomsWithEvents = json_decode(file_get_contents('results/updated_roomsWithEvents3.json'), true);

$foundPossibleRoom = [];
$timeSlots = range('A', 'E');
foreach ($students as &$student) {
    foreach ($roomsWithEvents as $eventIndex => &$roomsData) {
        $assignedRooms = $student['assignedRoom'];

        $usedEvents = array_keys($assignedRooms);

        $timeSlotsUsed = array_map(function ($room) {
            return substr($room, -1);
        }, $assignedRooms);

        $freeTimeSlots = array_diff($timeSlots, $timeSlotsUsed);


        $rooms = &$roomsData['rooms'];

        $availableRooms = array_filter($rooms, function ($room) use ($freeTimeSlots, $eventIndex, $usedEvents) {
            return $room['currentCapacity'] > 0 && in_array(substr($room['name'], -1), $freeTimeSlots) && !in_array($eventIndex, $usedEvents) ;
        });

        if (count($availableRooms) > 0) {
           // echo $student['firstname'] . " found event " . $eventIndex . " possible room to fill " . print_r( current($availableRooms), true) . "\n";
            $room = current($availableRooms);
            $student['assignedRoom'][$eventIndex] = $room['name'];

            $roomIndex = array_search($room, $rooms);
            $roomsData['rooms'][$roomIndex]['currentCapacity'] = $room['currentCapacity'] - 1;
        }

        if (count($student['assignedRoom']) === 5) {
            break;
        }
    }
}

file_put_contents('results/updated_roomsWithEvents4.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));
file_put_contents('results/updated_students4.json', json_encode($students, JSON_PRETTY_PRINT));
