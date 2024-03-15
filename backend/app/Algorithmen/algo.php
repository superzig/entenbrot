<?php

$studentsJson = file_get_contents('students.json');
$eventsJson = file_get_contents('events.json');

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
        'firstname' => $student['firstname'],
        'lastname' => $student['lastname'],
        'assignedEvents' => $student['assignedEvents'],
        'choices' => [
            $student['choice1'],
            $student['choice2'],
            $student['choice3'],
            $student['choice4'],
            $student['choice5'],
            $student['choice6'],
        ]
    ];
}, $students);

$eventsWithRoomCount = array_map(function ($event) {
    $roomsNeeded = ceil(count($event['assignedStudents']) / 20);
    return [
        'eventId' => $event['number'],
        'studentCount' => count($event['assignedStudents']),
        'totalCapacity' => $event['totalCapacity'],
        'roomsNeeded' => $roomsNeeded
    ];
}, $expandedEvents);

// Berechnen der Gesamtsumme der benötigten Räume
$totalRoomsNeeded = array_sum(array_column($eventsWithRoomCount, 'roomsNeeded'));

// Ausgabe der Studenten- und Eventlisten und der Gesamtzahl der benötigten Räume
file_put_contents('students2.json', json_encode([
    'students' => $studentsEvents,
]));

file_put_contents('events2.json', json_encode([
    'events' => $eventsWithRoomCount,
]));

