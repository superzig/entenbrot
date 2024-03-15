<?php

$studentsJson = file_get_contents('students.json');
$eventsJson = file_get_contents('events.json');

$students = json_decode($studentsJson, true);
$originalEvents = json_decode($eventsJson, true);

// Erweitere Events basierend auf ihrer maximalen Kapazität
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

    $choices = ['choice1', 'choice2', 'choice3', 'choice4', 'choice5', 'choice6'];

    foreach ($choices as $choiceIndex => $choice) {
        if (!empty($student[$choice])) {
            foreach ($expandedEvents as &$event) {
                if ($event['number'] == $student[$choice] && $assignStudentToEvent($student, $event)) {
                    break;
                }
            }
        }

        // Überprüfe, ob der Schüler bereits fünf Events hat
        if (count($student['assignedEvents']) == 5) {
            break;
        }
    }

    // Füge zufällige Events hinzu, wenn der Schüler noch keine 5 hat
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
            isset($student['choice1']) ? $student['choice1'] : null,
            isset($student['choice2']) ? $student['choice2'] : null,
            isset($student['choice3']) ? $student['choice3'] : null,
            isset($student['choice4']) ? $student['choice4'] : null,
            isset($student['choice5']) ? $student['choice5'] : null,
            isset($student['choice6']) ? $student['choice6'] : null,
        ]
    ];
}, $students);

$eventsWithStudentCount = array_map(function ($event) {
    return [
        'eventId' => $event['number'],
        'studentCount' => count($event['assignedStudents']),
        'totalCapacity' => $event['totalCapacity']
    ];
}, $expandedEvents);

// Ausgabe der Studenten- und Eventlisten
echo json_encode([
    'students' => $studentsEvents,
    'events' => $eventsWithStudentCount
]);

?>
