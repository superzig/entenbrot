<?php

$students = json_decode(file_get_contents('students.json'), true);
$events = json_decode(file_get_contents('events.json'), true);

function getEventsSlots($events) {
    $slots = ['A', 'B', 'C', 'D', 'E'];
    $slotIndices = array_flip($slots);
    $eventSlots = [];

    foreach ($events as $event) {
        $index = $slotIndices[$event['earliestDate']];
        $eventSlots[$event['number']] = array_slice($slots, $index, $event['eventMax']);
    }

    return $eventSlots;
}

$eventSlots = getEventsSlots($events);

$assignments = [];
foreach ($eventSlots as $eventNumber => $eventSlotsArray) {
    foreach ($eventSlotsArray as $slot) {
        $assignments[$eventNumber][$slot] = 20;
    }
}

function assignStudent(&$student, &$assignments, &$eventSlots) {
    $assignedCounter = 0;

    foreach ($student['choices'] as $choice) {
        if ($assignedCounter >= 5) {
            break;
        }

        if ($choice !== null && !in_array($choice, $student['assignedEvents'])) {
            foreach ($eventSlots[$choice] as $slot) {
                if ($assignments[$choice][$slot] > 0 && !in_array($slot, $student['assignedSlots'], true)) {
                    $student['assignedEvents'][] = $choice;
                    $student['assignedSlots'][] = $slot;
                    $assignments[$choice][$slot]--;
                    $assignedCounter++;
                    break; // Verlässt nur die innere Schleife
                }
            }
        }
    }

    // Sicherstellen, dass der Schüler zu 5 Events zugewiesen wird, auch wenn sie nicht seine Wünsche sind
    while ($assignedCounter < 5) {
        foreach ($assignments as $eventNum => $slots) {
            foreach ($slots as $slot => $capacity) {
                if ($capacity > 0 && !in_array($eventNum, $student['assignedEvents']) && !in_array($slot, $student['assignedSlots'])) {
                    $student['assignedEvents'][] = $eventNum;
                    $student['assignedSlots'][] = $slot;
                    $assignments[$eventNum][$slot]--;
                    $assignedCounter++;
                    break 2; // Verlässt beide Schleifen
                }
            }
        }
    }
}

foreach ($students as &$student) {
    $student['assignedEvents'] = [];
    $student['assignedSlots'] = [];
    $student['choices'] = array_filter([$student['choice1'], $student['choice2'], $student['choice3'], $student['choice4'], $student['choice5'], $student['choice6']]);

    assignStudent($student, $assignments, $eventSlots);
}

echo json_encode($students, JSON_PRETTY_PRINT);

?>
