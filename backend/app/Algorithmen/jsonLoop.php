<?php

$resultsData = json_decode(file_get_contents('results.json'), true);

// Initialisiere ein Array, um die Anzahl der Schüler pro Event zu zählen
$eventCounts = [];

// Zähle die Anzahl der Schüler pro Event
foreach ($resultsData as $result) {
    $eventNumber = $result['eventNumber'];
    if (array_key_exists($eventNumber, $eventCounts)) {
        $eventCounts[$eventNumber]++;
    } else {
        $eventCounts[$eventNumber] = 1;
    }
}

// Gib die Zählung als JSON aus
echo json_encode($eventCounts);
?>
