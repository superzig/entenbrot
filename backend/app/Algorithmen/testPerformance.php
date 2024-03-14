<?php

$students = json_decode(file_get_contents('students.json'), true);
$events = json_decode(file_get_contents('events.json'), true);

$medianTime = 0;
$loopCount = 10000;
$times = [];

for ($i = 0; $i < $loopCount; $i++) {
    $startTime = microtime(true);
    $result = runAlgo($students, $events);
    $endTime = microtime(true);
    $timeTook = $endTime - $startTime;
    $times[] = $timeTook;
    echo "Run " . $i . " took " . ($timeTook) . " seconds. \n";
}

// Calculate the median time out of all time took from 1000 runs
sort($times);
$medianTime = $times[$loopCount / 2];

echo "\n";
echo "The result is: ";
echo json_encode($result, JSON_PRETTY_PRINT);
echo "\n";
echo "The Algorithm took median " . $medianTime . " seconds to run. \n";
// 0.014003992080688 seconds
// 0.01399302482605 seconds
function runAlgo($students, $events)
{
    $slots = ['A', 'B', 'C', 'D', 'E'];
    $slotIndices = array_flip($slots);
    $eventSlots = [];

    foreach ($events as $event) {
        $index = $slotIndices[$event['earliestDate']];
        $eventSlots[$event['number']] = array_slice($slots, $index, $event['eventMax']);
    }

    $assignments = [];
    foreach ($eventSlots as $eventNumber => $eventSlotsArray) {
        foreach ($eventSlotsArray as $slot) {
            $assignments[$eventNumber][$slot] = 20;
        }
    }

    foreach ($students as &$student) {
        $student['assignedEvents'] = [];
        $student['assignedSlots'] = [];
        $student['choices'] = array_filter([$student['choice1'], $student['choice2'], $student['choice3'], $student['choice4'], $student['choice5'], $student['choice6']]);
        $slotsAssigned = [];

        while (count($student['assignedEvents']) < 5) {
            if (!assignStudent($student, $assignments, $eventSlots)) {
                break;
            }
        }
    }

    return $students;
}

// Funktion zur Zuweisung der Schüler
function assignStudent(&$student, &$assignments, &$eventSlots) {
    foreach ($student['choices'] as $choice) {
        if ($choice === null || in_array($choice, $student['assignedEvents'])) continue;

        foreach ($eventSlots[$choice] as $slot) {
            if ($assignments[$choice][$slot] > 0 && !in_array($slot, $student['assignedSlots'], true)) {
                $student['assignedEvents'][] = $choice;
                $student['assignedSlots'][] = $slot;
                $assignments[$choice][$slot]--;
                break 2; // Breaks both foreach loops
            }
        }
    }
}


