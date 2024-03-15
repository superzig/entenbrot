<?php

$roomsWithEvents = json_decode(file_get_contents('results/updated_roomsWithEvents4.json'), true);

foreach ($roomsWithEvents as &$roomsWithEvent) {
    $rooms = &$roomsWithEvent['rooms'];

    $rooms = array_filter($rooms, function ($room) {
        return $room['currentCapacity'] !== $room['maxCapacity'];
    });
}

file_put_contents('results/updated_roomsWithEvents5.json', json_encode($roomsWithEvents, JSON_PRETTY_PRINT));
