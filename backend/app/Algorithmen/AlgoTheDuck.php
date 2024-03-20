<?php
$startTime = microtime(true);
$studentsJson = file_get_contents('data/students.json');
$eventsJson = file_get_contents('data/events.json');

$students = json_decode($studentsJson, true);
$originalEvents = json_decode($eventsJson, true);

$expandedEvents = [];
foreach ($originalEvents as $event) {
    $event['assignedStudents'] = [];
    $event['totalCapacity'] = $event['eventMax'] * $event['participants'];
    $expandedEvents[] = $event;
}

foreach ($students as &$student) {
    $student['assignedEvents'] = [];

    $assignStudentToEvent = function (&$student, &$event) {
        if (count($event['assignedStudents']) < $event['totalCapacity']) {
            $event['assignedStudents'][] = $student['firstname'] . ' ' . $student['lastname'];
            $student['assignedEvents'][] = $event['number'];
            return true;
        }
        return false;
    };

    foreach (['choice1', 'choice2', 'choice3', 'choice4', 'choice5', 'choice6'] as $choice) {
        if (!empty($student[$choice])) {
            foreach ($expandedEvents as &$event) {
                if ($event['number'] == $student[$choice] && $assignStudentToEvent($student, $event)) {
                    break;
                }
            }
        }

        if (count($student['assignedEvents']) == 5) {
            break;
        }
    }

    while (count($student['assignedEvents']) < 5) {
        $availableEvents = array_filter($expandedEvents, function ($event) use ($student) {
            return count($event['assignedStudents']) < $event['totalCapacity'] && !in_array($event['number'], $student['assignedEvents']);
        });

        if (empty($availableEvents)) {
            break;
        }

        $randomEventKey = array_rand($availableEvents);
        $assignStudentToEvent($student, $availableEvents[$randomEventKey]);
    }
}

$studentsEvents = array_map(function ($student) {
    return [
        'firstname'      => $student['firstname'],
        'lastname'       => $student['lastname'],
        'assignedEvents' => $student['assignedEvents'],
        'choices'        => [
            $student['choice1'],
            $student['choice2'],
            $student['choice3'],
            $student['choice4'],
            $student['choice5'],
            $student['choice6'],
        ],
    ];
}, $students);

$eventsWithRoomCount = array_map(function ($event) {
    $roomsNeeded = ceil(count($event['assignedStudents']) / 20);
    return [
        'eventId'       => $event['number'],
        'studentCount'  => count($event['assignedStudents']),
        'totalCapacity' => $event['totalCapacity'],
        'roomsNeeded'   => $roomsNeeded,
    ];
}, $expandedEvents);

// Berechnen der Gesamtsumme der benötigten Räume
$totalRoomsNeeded = array_sum(array_column($eventsWithRoomCount, 'roomsNeeded'));

// Ausgabe der Studenten- und Eventlisten und der Gesamtzahl der benötigten Räume
file_put_contents('results/students.json', json_encode([
    'students' => $studentsEvents,
], JSON_PRETTY_PRINT));

file_put_contents('results/events.json', json_encode([
    'events' => $eventsWithRoomCount,
], JSON_PRETTY_PRINT));

// Daten aus JSON-Dateien einlesen
$roomsJson = file_get_contents('data/rooms.json');
$eventsJson = file_get_contents('results/events.json');

// In PHP-Arrays umwandeln
$rooms = json_decode($roomsJson, true);
$events = json_decode($eventsJson, true)['events'];

// Array zur Zuordnung von Räumen zu Events initialisieren
$eventRoomAssignments = [];

// Durch alle Events gehen und Räume zuweisen
foreach ($events as $event) {
    $roomsNeeded = $event['roomsNeeded'];
    $eventRoomAssignments[$event['eventId']] = [
        'rooms'         => [],
        'totalStudents' => $event['studentCount'],
        'totalCapacity' => 0,
    ];

    foreach ($rooms as $key => $room) {
        if ($roomsNeeded == 0) {
            break;
        }

        // Raum dem Event zuweisen und aus der Liste verfügbarer Räume entfernen
        $eventRoomAssignments[$event['eventId']]['rooms'][] = [
            'name'            => $room['name'],
            'currentCapacity' => $room['capacity'],
            'maxCapacity'     => $room['capacity'],
        ];
        $eventRoomAssignments[$event['eventId']]['totalCapacity'] += $room['capacity'];

        unset($rooms[$key]);
        $roomsNeeded--;
    }
}

// Ergebnis als JSON ausgeben
file_put_contents('results/roomsWithEvents.json', json_encode($eventRoomAssignments, JSON_PRETTY_PRINT));

// Load and decode JSON data
$studentsJson = file_get_contents('results/students.json');
$roomsJson = file_get_contents('results/roomsWithEvents.json');

$students = json_decode($studentsJson, true)['students'];
$roomsWithEvents = json_decode($roomsJson, true);

// Iterate over students
foreach ($students as &$student) {
    $usedLetters = [];

    // Iterate over assigned events for each student
    foreach ($student['assignedEvents'] as $event) {
        foreach ($roomsWithEvents as $eventKey => &$roomEvent) {
            if ($eventKey == $event) {
                // Find a suitable room based on the last letter
                foreach ($roomEvent['rooms'] as &$room) {
                    if ($room['currentCapacity'] == 0) {
                        continue;
                    }
                    $lastLetter = substr($room['name'], -1);

                    // Check if the letter has not been used and assign the room
                    if (!in_array($lastLetter, $usedLetters)) {
                        $student['assignedRoom'][$event] = $room['name'];
                        $room['currentCapacity'] -= 1;
                        $usedLetters[] = $lastLetter;
                        break;
                    }
                    if (in_array($lastLetter, $usedLetters)) {
                        $student['unAssignedRoom'][$event] = $room['name'];
                        $usedLetters[] = $lastLetter;
                        break;
                    }
                }
            }
        }
    }
}

// Output the result
file_put_contents('results/roomsWithEvents.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));
file_put_contents('results/students.json', json_encode($students, JSON_PRETTY_PRINT));

$students = json_decode(file_get_contents('results/students.json'), true);
$roomsWithEvents = json_decode(file_get_contents('results/roomsWithEvents.json'), true);
$foundPossibleRoom = [];

foreach ($students as &$student) {

    if (!isset($student['unAssignedRoom'])) {
        continue;
    }
    $unassignedRooms = $student['unAssignedRoom'];

    foreach ($unassignedRooms as $eventIndex => $unassignedRoomValue) {
        if (isset($roomsWithEvents[$eventIndex])) {
            $event = &$roomsWithEvents[$eventIndex];
            foreach ($event['rooms'] as &$room) {
                $eventRoomValue = $room['name'];
                if ($eventRoomValue != $unassignedRoomValue) {

                    // get me the last letter of $name
                    $eventAvailableTimeSlot = substr($eventRoomValue, -1);

                    foreach ($student['assignedRoom'] as $assignedRoomValue) {
                        $assignedUsedTimeSlot = substr($assignedRoomValue, -1);

                        if ($eventAvailableTimeSlot !== $assignedUsedTimeSlot && $room['currentCapacity'] > 0) {
                            $foundPossibleRoom = [$eventAvailableTimeSlot, $eventRoomValue];
                        } else {
                            $foundPossibleRoom = [];
                            break;
                        }
                    }

                    if (count($foundPossibleRoom) > 0) {
                        // array deconstruction to get the values
                        [$eventAvailableTimeSlot, $eventRoomValue] = $foundPossibleRoom;
                        // we can use this timeslot and update the currentCapacity of the room
                        $student['assignedRoom'][(string)$eventIndex] = $eventRoomValue;
                        $room['currentCapacity'] = $room['currentCapacity'] - 1;
                        $student['unAssignedRoom'] = array_diff($student['unAssignedRoom'], [$unassignedRoomValue]);
                        $foundPossibleRoom = [];
                        break;
                    }

//                    echo "could not assign student " . $student['firstname'] . " to the room \n" . $unassignedRoomValue. " for event " . $eventIndex . "\n";
                }
            }
        }
    }
}

file_put_contents('results/roomsWithEvents.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));
file_put_contents('results/students.json', json_encode($students, JSON_PRETTY_PRINT));

$students = json_decode(file_get_contents('results/students.json'), true);
$roomsWithEvents = json_decode(file_get_contents('results/roomsWithEvents.json'), true);

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
            return $room['currentCapacity'] > 0 && in_array(substr($room['name'], -1), $freeTimeSlots) && !in_array($eventIndex, $usedEvents);
        });

        if (count($availableRooms) > 0) {
            // echo $student['firstname'] . " found event " . $eventIndex . " possible room to fill " . print_r( current($availableRooms), true) . "\n";
            $room = current($availableRooms);
            $student['assignedRoom'][$eventIndex] = $room['name'];

            $roomIndex = array_search($room, $rooms);
            $referencedRoom = &$roomsData['rooms'][$roomIndex];
            $referencedRoom['currentCapacity'] = $referencedRoom['currentCapacity'] - 1;
            // echo "assigned room: " . $room['name'] . " for student " . $student['firstname'] . " " . $student['lastname'] . "\n";
            // echo "current capacity: " . $referencedRoom['currentCapacity'] . "\n";
            continue;
        }

        if (count($student['assignedRoom']) === 5) {
            break;
        }
    }
}

file_put_contents('results/roomsWithEvents.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));
file_put_contents('results/students.json', json_encode($students, JSON_PRETTY_PRINT));

$roomsWithEvents = json_decode(file_get_contents('results/roomsWithEvents.json'), true);

foreach ($roomsWithEvents as &$roomsWithEvent) {
    $rooms = &$roomsWithEvent['rooms'];

    $rooms = array_filter($rooms, function ($room) {
        return $room['currentCapacity'] !== $room['maxCapacity'];
    });
}

file_put_contents('results/roomsWithEvents.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));

$endTime = microtime(true);

$difference = $endTime - $startTime;

echo "The script took " . $difference . " seconds to run";

echo "Testing results of algorithm... \n";

/* #########
 * # TESTING
 * #########
 */

$studentsTest = json_decode(file_get_contents('results/students.json'), true);
$rooms = json_decode(file_get_contents('data/rooms.json'), true);
$assignedRooms = [];
foreach ($studentsTest as $studentTest) {

    if (count($studentTest['assignedRoom']) < 5) {
        echo "Student " . $studentTest['firstname'] . " " . $studentTest['lastname'] . " has not been assigned to 5 rooms \n";
    }

    // check if each time slot of the student assignedRooms is unique
    $timeSlots = array_map(static function ($room) {
        return substr($room, -1);
    }, $studentTest['assignedRoom']);

    if (count($timeSlots) !== count(array_unique($timeSlots))) {
        echo "Student " . $studentTest['firstname'] . " " . $studentTest['lastname'] . " has duplicate time slots \n";
    }

    foreach ($studentTest['assignedRoom'] as $event => $room) {
        if (!isset($assignedRooms[$room]['count'])) {
            $assignedRooms[$room]['count'] = 0;
        }
        $assignedRooms[$room]['count'] = $assignedRooms[$room]['count'] + 1;

        $assignedRooms[$room][] = [
            'firstname' => $studentTest['firstname'],
            'lastname'  => $studentTest['lastname'],
        ];
    }
}

unset($roomsWithEvent, $originalEvents);
$roomsWithEvent = json_decode(file_get_contents('results/roomsWithEvents.json'), true);
$originalEvents = json_decode(file_get_contents('data/events.json'), true);

// check if the assignedRooms count is not greater than the "capacity" in the $rooms array based on the "name" key
foreach ($assignedRooms as $room => $data) {
    $maxCapacity = $rooms[array_search($room, array_column($rooms, 'name'))]['capacity'];
    if ($data['count'] > $maxCapacity) {
        echo "[!!!] Room " . $room . " has " . $data['count'] . " students but the capacity is $maxCapacity \n";
    }

    foreach ($roomsWithEvent as $eventIndex => $roomWithEvent) {
        if (!isset($roomWithEvent['rooms'])) {
            continue;
        }
        $foundRoom = array_filter($roomWithEvent['rooms'], static function ($roomObject) use ($room) {
            return $roomObject['name'] === $room;
        });

        if (current($foundRoom)) {

            $originalEvent = array_filter($originalEvents, static function ($originalEvent) use ($eventIndex) {
                return $originalEvent['number'] == $eventIndex;
            });

            if (!current($originalEvent)) {
                break;
            }
            $originalEvent = current($originalEvent);
            $maxParticipants = $originalEvent['participants'];

            if ($maxCapacity > $maxParticipants) {
                echo "[!!!] Room " . $room . " has " . $data['count'] . " students but the event $eventIndex has an max of $maxParticipants participants \n";
            }
            break;
        }
    }
}


file_put_contents("results/test_assignedRooms.json", json_encode($assignedRooms, JSON_PRETTY_PRINT));
