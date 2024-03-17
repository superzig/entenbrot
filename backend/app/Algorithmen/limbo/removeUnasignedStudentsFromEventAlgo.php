<?php

$roomsWithEventsJson = file_get_contents('results/roomsWithEvents.json');
$roomsWithEvents = json_decode($roomsWithEventsJson, true);

$updatedStudentsJson = file_get_contents('results/updated_students2.json');
$updatedStudents = json_decode($updatedStudentsJson, true);

foreach ($updatedStudents as $student) {
    // Check if unassignedRoom is empty (meaning we should process this student)
    if (empty($student['unassignedRoom'])) {
        foreach ($student['assignedRoom'] as $eventId => $roomName) {
            // Find the corresponding event and room to decrement the student count
            foreach ($roomsWithEvents[$eventId]['rooms'] as $key => $room) {
                if ($room['name'] === $roomName) {
                    $roomsWithEvents[$eventId]['totalStudents']--;
                    break; // Break as we found the matching room and updated the count
                }
            }
        }
    }
}

// Output the updated JSON
file_put_contents('results/updated_roomsWithEvents.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));
?>
