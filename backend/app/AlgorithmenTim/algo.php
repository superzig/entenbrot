<?php

/* DATA CREATION START */
function readExcelFile($filePath)
{
    $data = [];

    if (($handle = fopen($filePath, 'rb')) !== false) {
        // Lese die Überschriften (erste Zeile) und entferne das UTF-8 BOM-Zeichen
        $headers = str_getcsv(str_replace("\xEF\xBB\xBF", '', fgets($handle)), ";");

        // Iteriere über die Daten und erstelle das assoziative Array
        while (($row = fgetcsv($handle, 1000, ";")) !== false) {
            $rowData = [];
            foreach ($headers as $index => $header) {
                // Entferne nicht druckbare Zeichen
                $cellValue = filter_var($row[$index], FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

                // Standardisiere die Zeichenkodierung und verwende "ä" direkt
                $rowData[$header] = utf8_encode($cellValue);
            }
            $data[] = $rowData;
        }
        fclose($handle);
    } else {
        die("Fehler beim Öffnen der Datei.");
    }


    return $data;
}

/* DATA CREATION END */
$roomDataRaw = readExcelFile('imports\raumliste.csv');
$roomData = array();
foreach ($roomDataRaw as $data) {
    $roomData[$data["roomID"]] = $data;
}

$eventDataRaw = readExcelFile('imports\veranstaltungsliste.csv');
$eventData = array();
foreach ($eventDataRaw as $data) {
    $eventData[$data["eventID"]] = $data;
}

$studentDataRaw = readExcelFile('imports\schuelerwahlliste.csv');

$studentData = array();
$counter = 1;
foreach ($studentDataRaw as $data) {
    $studentData[$counter] = $data;
    $counter++;
}

//Vorbelegung der Zuweisungsslots (Kann später i wo in einen anderen schritt eingebunden werden.)
$assignment = array();
foreach ($studentData as $key => $array) {
    $assignment[$key]["A"] = null;
    $assignment[$key]["B"] = null;
    $assignment[$key]["C"] = null;
    $assignment[$key]["D"] = null;
    $assignment[$key]["E"] = null;
}

$amountChoises = getAmountChoices($studentData);
$eventToRoomAssignment = eventToRoomAssignment($roomData, $eventData, $amountChoises["allChoices"]);
$amountEventSpace = array();
foreach ($eventToRoomAssignment as $roomID => $assignmentArray) {
    foreach ($assignmentArray as $timeslot => $eventID) {
        $roomCapacity = $roomData[$roomID]["capacity"];
        $eventCapacity = $eventData[$eventID]["maxParticipants"];
        $realCapacity = ($roomCapacity < $eventCapacity) ? $roomCapacity : $eventCapacity;
        $spaceInEventLeft[$eventID][$roomID][$timeslot] = $realCapacity;
    }
}

for ($i = 1; $i <= 6; $i++) {
    foreach ($studentData as $studentID => $studentDataArray) {
        foreach ($studentDataArray as $studentDataArrayField => $eventID) {
            if ($studentDataArrayField === "choice_" . $i) {
                if ($studentDataArray["choice_" . $i] != null) {
                    foreach ($spaceInEventLeft[$studentDataArray["choice_" . $i]] as $roomID => $eventAssignmentArray) {
                        foreach ($eventAssignmentArray as $timeslot => $capacity) {
                            if ($capacity > 0 && !isset($assignment[$studentID][$timeslot])) {
                                $assignment[$studentID][$timeslot] = $eventID;
                                $spaceInEventLeft[$eventID][$roomID][$timeslot]--;
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
}

foreach ($assignment as $studentID => $studentTimeslotsToEventsAssignmentArray) {
    foreach ($studentTimeslotsToEventsAssignmentArray as $timeslot => $eventID) {
        if ($eventID === null) {
            $mostSpaceEventID = findEventWithMostSpaceLeft($spaceInEventLeft, $timeslot, $studentTimeslotsToEventsAssignmentArray);
            $assignment[$studentID][$timeslot] = $mostSpaceEventID;
            $spaceInEventLeft[$mostSpaceEventID][array_key_first($spaceInEventLeft[$mostSpaceEventID])][$timeslot]--;
        }
    }
}

// StudentID to timeslot to EventID!
$json = json_encode($assignment, JSON_PRETTY_PRINT);
echo ("<br><br>StudentID to timeslot to EventID! : <br><br>");
echo ($json);
echo ("<br><br><br><br>");

// RoomID to timeslot to EventID!
$json = json_encode($eventToRoomAssignment, JSON_PRETTY_PRINT);
echo ("<br><br>RoomID to timeslot to EventID!  : <br><br>");
echo ($json);
echo ("<br><br><br><br>");
exit();

// Optimierungsbedarf !
function getAmountChoices($studentData)
{
    $amountChoices = array();
    for ($i = 1; $i <= 6; $i++) {
        foreach ($studentData as $id => $array) {
            $choiceField = "choice_" . $i;

            if (!empty($array[$choiceField])) {
                if (isset($amountChoices[$choiceField][$array[$choiceField]])) {
                    $amountChoices[$choiceField][$array[$choiceField]] += 1;
                } else {
                    $amountChoices[$choiceField][$array[$choiceField]] = 1;
                }
            } else {
                if (isset($amountChoices[$choiceField]["null"])) {
                    $amountChoices[$choiceField]["null"] += 1;
                } else {
                    $amountChoices[$choiceField]["null"] = 1;
                }
            }
        }
    }

    foreach ($studentData as $array) {
        foreach ($array as $key => $value) {
            if (substr($key, 0, 7) === "choice_") {
                if (!empty($value)) {
                    if (!empty($amountChoices["allChoices"][$value])) {
                        $amountChoices["allChoices"][$value] += 1;
                    } else {
                        $amountChoices["allChoices"][$value] = 1;
                    }
                } else {
                    if (isset($amountChoices["allChoices"]["null"])) {
                        $amountChoices["allChoices"]["null"] += 1;
                    } else {
                        $amountChoices["allChoices"]["null"] = 1;
                    }
                }
            }
        }
    }
    return $amountChoices;
}

function getAvailableTimeSlots($raumZuweisung, $timeslot, $roomID, $requiredSeats)
{
    $availableSlots = [];
    $timeSlots = ['A', 'B', 'C', 'D', 'E'];

    // Finde den Startindex des gegebenen Timeslots
    $startIndex = array_search($timeslot, $timeSlots);

    // Durchlaufe die Timeslots
    for ($i = $startIndex; $i <= count($timeSlots) - $requiredSeats; $i++) {
        $isConsecutive = true;

        // Überprüfe, ob die erforderliche Anzahl aufeinanderfolgender Timeslots frei ist
        for ($j = 0; $j < $requiredSeats; $j++) {
            if (isset($raumZuweisung[$roomID][$timeSlots[$i + $j]])) {
                $isConsecutive = false;
                break;
            }
        }

        // Wenn aufeinanderfolgende Timeslots gefunden wurden, füge sie dem Ergebnis hinzu
        if ($isConsecutive) {
            $availableSlots = array_slice($timeSlots, $i, $requiredSeats);
            break;
        }
    }

    return $availableSlots;
}

function anzahlAufeinanderFolgendeSlotsFrei($raumZuweisung, $startTimeslot, $roomID)
{
    $maxConsecutive = 0;
    $currentConsecutive = 0;
    $timeSlots = ['A', 'B', 'C', 'D', 'E'];

    $startIndex = array_search($startTimeslot, $timeSlots);

    foreach ($timeSlots as $index => $timeslot) {
        if ($index >= $startIndex) {
            if (!isset($raumZuweisung[$roomID][$timeslot])) {
                $currentConsecutive++;
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
            } else {
                $currentConsecutive = 0;
            }
        }
    }

    return $maxConsecutive;
}

function eventToRoomAssignment($roomData, $eventData, $amountChoisesAll)
{

    $raumZuweisung = array();
    uasort($roomData, "sortByCapacity");
    uasort($eventData, "sortByParticipants");

    foreach ($eventData as $eventID => $eventDataArray) {

        $raumFuerEvent = null;

        $teilnehmerAnzahl = $amountChoisesAll[$eventID];
        $maximaleTeilnehmerAnzahlProEvent = $eventDataArray["maxParticipants"];

        foreach ($roomData as $roomID => $roomDataArray) {

            $teilnehmerAnzahlProEvent = ($maximaleTeilnehmerAnzahlProEvent > $teilnehmerAnzahl) ? $teilnehmerAnzahl : $maximaleTeilnehmerAnzahlProEvent;
            $teilnehmerAnzahlProEvent = ($teilnehmerAnzahlProEvent > $roomDataArray["capacity"]) ? $roomDataArray["capacity"] : $teilnehmerAnzahlProEvent;
            $anzahlEventsUmAlleWuenscheZuErfuellen = ceil($teilnehmerAnzahl / $teilnehmerAnzahlProEvent);
            $anzahlEvents = ($anzahlEventsUmAlleWuenscheZuErfuellen > $eventDataArray["amountEvents"]) ? $eventDataArray["amountEvents"] : $anzahlEventsUmAlleWuenscheZuErfuellen;

            if (anzahlAufeinanderFolgendeSlotsFrei($raumZuweisung, $eventDataArray["earliestTimeSlot"], $roomID) >= $anzahlEvents) {
                $raumFuerEvent = $roomID;
                break;
            }
        }

        foreach (getAvailableTimeSlots($raumZuweisung, $eventDataArray["earliestTimeSlot"], $raumFuerEvent, $anzahlEvents) as $timeslot) {
            $raumZuweisung[$raumFuerEvent][$timeslot] = $eventID;
        }
    }
    return $raumZuweisung;
}

//This function is searching the event with the most avalible places for the given timeslot which is not alrady inside the student assignment.
function findEventWithMostSpaceLeft($spaceInEventLeft, $timeslot, $studentTimeslotsToEventsAssignmentArray)
{
    $maxSpaceLeft = null;
    $eventWithMostSpaceLeft = null;
    foreach ($spaceInEventLeft as $eventID => $roomToTimeslotsToSpaceLeft) {

        foreach ($roomToTimeslotsToSpaceLeft as $values) {

            if (isset($values[$timeslot])) {
                if (!in_array($eventID, $studentTimeslotsToEventsAssignmentArray)) {
                    if ($maxSpaceLeft === null || $values[$timeslot] > $maxSpaceLeft) {
                        $maxSpaceLeft = $values[$timeslot];
                        $eventWithMostSpaceLeft = $eventID;
                    }
                }
            }
        }
    }

    return $eventWithMostSpaceLeft;
}

/* HELER FUNCTIONS START */
function sortByCapacity($a, $b)
{
    return $b["capacity"] - $a["capacity"];
}

function sortByParticipants($a, $b)
{
    return $b['maxParticipants'] - $a['maxParticipants'];
}
/* HELER FUNCTIONS END */
