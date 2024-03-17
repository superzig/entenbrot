<?php

// Daten aus JSON-Dateien einlesen
$roomsJson = file_get_contents('data/rooms.json');
$eventsJson = file_get_contents('results/events2.json');

// In PHP-Arrays umwandeln
$rooms = json_decode($roomsJson, true);
$events = json_decode($eventsJson, true)['events'];

// Array zur Zuordnung von Räumen zu Events initialisieren
$eventRoomAssignments = [];

// Durch alle Events gehen und Räume zuweisen
foreach ($events as $event) {
    $roomsNeeded = $event['roomsNeeded'];
    $eventRoomAssignments[$event['eventId']] = [
        'rooms' => [],
        'totalStudents' => $event['studentCount'],
        'totalCapacity' => 0
    ];

    foreach ($rooms as $key => $room) {
        if ($roomsNeeded == 0) {
            break;
        }

        // Raum dem Event zuweisen und aus der Liste verfügbarer Räume entfernen
        $eventRoomAssignments[$event['eventId']]['rooms'][] = [
            'name' => $room['name'],
            'currentCapacity' => $room['capacity'],
            'maxCapacity' => $room['capacity']
        ];
        $eventRoomAssignments[$event['eventId']]['totalCapacity'] += $room['capacity'];

        unset($rooms[$key]);
        $roomsNeeded--;
    }
}

// Ergebnis als JSON ausgeben
file_put_contents('results/roomsWithEvents.json', json_encode($eventRoomAssignments, JSON_PRETTY_PRINT));
