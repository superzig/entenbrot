<?php

$students = json_decode(file_get_contents('results/students.json'), true);

$scorePoints = [
    1 => 6,
    2 => 5,
    3 => 4,
    4 => 3,
    5 => 2,
    6 => 1
];
$maxPoints = 20;

// calculate the total points possible for all students together based on their choices, the maximum points possible is 20 and the minimum is 0. Only count 5 choices from the students, from highest to lowest.
$totalPoints = 0;
foreach ($students as $student) {
    $choices = $student['choices'];

    $score = 0;
    for ($i = 1; $i <= 5; $i++) {
        if (array_key_exists($i, $choices)) {
            $score += $scorePoints[$i];
        }
    }

    $totalPoints += $score;
}

$scores = [];
$reachedPoints = 0;
$notGrantedChoices = [];
foreach ($students as $student) {
    $choices = $student['choices'];
    $assignedRooms = $student['assignedRoom'];
    $grantedChoices = [];
    // get the count how many choices keys are the keys of assignedRooms
    $score = 0;
    foreach ($choices as $choiceNumber => $eventChoice) {

        if (array_key_exists($eventChoice, $assignedRooms)) {
            $count++;
            $score += $scorePoints[$choiceNumber+1];
            $grantedChoices[$eventChoice] = $scorePoints[$choiceNumber+1];
        } else {
            $notGrantedChoices[$student['firstname'] . '-' . $student['lastname']][$eventChoice] = [
                $scorePoints[$choiceNumber+1]
            ];
        }
    }

    if (count($grantedChoices) == 5) {
        unset($notGrantedChoices[$student['firstname'] . '-' . $student['lastname']]);
    }

    $reachedPoints += $score;
}

echo "Total points: " . $totalPoints . "\n";
echo "Reached points: " . $reachedPoints . "\n";

$percentage = ($reachedPoints / $totalPoints) * 100;
echo "Percentage: " . round($percentage, 2) . "%\n";

//echo "Not granted choices: \n";
//print_r($notGrantedChoices);
