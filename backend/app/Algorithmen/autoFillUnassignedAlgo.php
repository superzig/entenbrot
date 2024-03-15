<?php
$students = json_decode(file_get_contents('results/updated_students2.json'), true);
$roomsWithEvents = json_decode(file_get_contents('results/roomsWithEvents.json'), true);

foreach ($students as &$student) {

    if (!isset($student['unAssignedRoom'])) {
        continue;
    }
    $unassignedRooms = $student['unAssignedRoom'];
    foreach ($unassignedRooms as $eventIndex => $unassignedRoomValue)
    {
        if (isset($roomsWithEvents[$eventIndex])) {
            $event = &$roomsWithEvents[$eventIndex];

            foreach ($event['rooms'] as &$room) {
                $eventRoomValue = $room['name'];
                if ($eventRoomValue != $unassignedRoomValue) {

                    // get me the last letter of $name
                    $eventAvailableTimeSlot = substr($eventRoomValue, -1);
                    $foundPossibleRoom = [];
                    foreach ($student['assignedRoom'] as $assignedRoomValue) {
                        $assignedUsedTimeSlot = substr($assignedRoomValue, -1);

                        if ($eventAvailableTimeSlot !== $assignedUsedTimeSlot) {
                            $foundPossibleRoom = [$eventAvailableTimeSlot, $eventRoomValue];

                        } else {
                            $foundPossibleRoom = [];
                        }
                    }

                    if (count($foundPossibleRoom) > 0) {
                        // array deconstruction to get the values
                        [$eventAvailableTimeSlot, $eventRoomValue] = $foundPossibleRoom;
                        // we can use this timeslot and update the currentCapacity of the room
                        $student['assignedRoom'][(string) $eventIndex] = $eventRoomValue;
                        $room['currentCapacity'] = $room['currentCapacity'] - 1;
                        $student['unAssignedRoom'] = array_diff($student['unAssignedRoom'], [$unassignedRoomValue]);
                        break;
                    }
                }
            }
        }
    }
}

file_put_contents('results/updated_roomsWithEvents3.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));
file_put_contents('results/updated_students3.json', json_encode($students, JSON_PRETTY_PRINT));
