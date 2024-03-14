<?php

// Load data from JSON files
$students = json_decode(file_get_contents('students.json'), true);
$events = json_decode(file_get_contents('events.json'), true);
$rooms = json_decode(file_get_contents('rooms.json'), true);

// Initialize data structures for event availability and assignments
$assignments = [];
$eventAvailability = initializeEventAvailability($events);
$roomAvailability = initializeRoomAvailability($rooms);

// Assign students to events based on their preferences
foreach ($students as &$student) {
    $usedTimeslots = [];  // Track used timeslots for each student
    assignEventsToStudent($student, $eventAvailability, $assignments, $usedTimeslots, $roomAvailability);
}

// Output the final assignments as JSON
echo json_encode($assignments);

// Functions
function initializeEventAvailability($events) {
    $availability = [];
    foreach ($events as $event) {
        $availability[$event['number']] = [
            'capacity' => $event['participants'],
            'maxOccurrences' => $event['eventMax'],
            'timeslots' => range('A', 'E'),
            'earliestDate' => $event['earliestDate'],
            'assignedRooms' => [],
        ];
    }
    return $availability;
}

function initializeRoomAvailability($rooms) {
    $availability = [];
    foreach ($rooms as $room) {
        $availability[$room['name']] = [
            'capacity' => $room['capacity'],
            'timeslots' => array_fill_keys(range('A', 'E'), true), // true means available
        ];
    }
    return $availability;
}

function findAvailableTimeslot($timeslots, $earliestDate, &$usedTimeslots) {
    foreach ($timeslots as $timeslot) {
        if ($timeslot >= $earliestDate && !in_array($timeslot, $usedTimeslots)) {
            $usedTimeslots[] = $timeslot;
            return $timeslot;
        }
    }
    return false;
}

function findAvailableRoom(&$roomAvailability, $eventCapacity, $timeslot) {
    foreach ($roomAvailability as $roomName => &$room) {
        if ($room['capacity'] >= $eventCapacity && $room['timeslots'][$timeslot]) {
            $room['timeslots'][$timeslot] = false;
            return $roomName;
        }
    }
    return false;
}

function assignEventsToStudent(&$student, &$eventAvailability, &$assignments, &$usedTimeslots, &$roomAvailability) {
    $assignedEvents = [];
    for ($i = 1; $i <= 6; $i++) {
        $choiceKey = 'choice' . $i;
        if (isset($student[$choiceKey]) && !isset($assignedEvents[$student[$choiceKey]]) && count($assignedEvents) < 5) {
            $eventNumber = $student[$choiceKey];
            $event = &$eventAvailability[$eventNumber];

            if ($event['capacity'] > 0 && $event['maxOccurrences'] > 0) {
                $timeslot = findAvailableTimeslot($event['timeslots'], $event['earliestDate'], $usedTimeslots);
                if ($timeslot !== false) {
                    $roomName = findAvailableRoom($roomAvailability, $event['capacity'], $timeslot);
                    if ($roomName !== false) {
                        $assignments[$student['class']][$student['firstname'] . ' ' . $student['lastname']][] = [
                            'event' => $eventNumber,
                            'timeslot' => $timeslot,
                            'room' => $roomName,
                        ];
                        $assignedEvents[$eventNumber] = true;
                        $event['assignedRooms'][$timeslot] = $roomName;

                        $event['capacity']--;
                        $event['maxOccurrences']--;
                    }
                }
            }
        }
    }

    assignRandomEvents($student, $assignedEvents, $eventAvailability, $assignments, $usedTimeslots, $roomAvailability);
}

function assignRandomEvents(&$student, &$assignedEvents, &$eventAvailability, &$assignments, &$usedTimeslots, &$roomAvailability) {
    while (count($assignedEvents) < 5) {
        $changesMade = false;

        foreach ($eventAvailability as $eventNumber => &$event) {
            if (count($assignedEvents) >= 5) {
                break;
            }

            if ($event['capacity'] > 0 && $event['maxOccurrences'] > 0 && !isset($assignedEvents[$eventNumber])) {
                $timeslot = findAvailableTimeslot($event['timeslots'], $event['earliestDate'], $usedTimeslots);
                if ($timeslot !== false) {
                    $roomName = findAvailableRoom($roomAvailability, $event['capacity'], $timeslot);
                    if ($roomName !== false) {
                        $assignments[$student['class']][$student['firstname'] . ' ' . $student['lastname']][] = [
                            'event' => $eventNumber,
                            'timeslot' => $timeslot,
                            'room' => $roomName,
                        ];
                        $assignedEvents[$eventNumber] = true;
                        $event['assignedRooms'][$timeslot] = $roomName;

                        $event['capacity']--;
                        $event['maxOccurrences']--;
                        $changesMade = true;
                    }
                }
            }
        }

        if (!$changesMade) {
            break; // Break if no changes were made to prevent infinite loop
        }
    }
}
