<?php
/* READ DUMMY DATA */
try {
    $roomData = json_decode(file_get_contents('imports\rooms.json'), JSON_PRETTY_PRINT, 512, JSON_THROW_ON_ERROR);
    $eventData = json_decode(file_get_contents('imports\events.json'), JSON_PRETTY_PRINT, 512, JSON_THROW_ON_ERROR);
    $studentData = json_decode(file_get_contents('imports\students.json'), JSON_PRETTY_PRINT, 512, JSON_THROW_ON_ERROR);
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

$timeslotToTime = [
    "A" => "08:45 - 9:30",
    "B" => "09:50 - 10:35",
    "C" => "10:35 - 11:20",
    "D" => "11:40 - 12:25",
    "E" => "12:25 - 13:10",
];


//Vorbelegung der Zuweisungsslots (Kann später i wo in einen anderen schritt eingebunden werden.)
$assignment = [];
foreach ($studentData as $key => $array) {
    $assignment[$key]["A"] = null;
    $assignment[$key]["B"] = null;
    $assignment[$key]["C"] = null;
    $assignment[$key]["D"] = null;
    $assignment[$key]["E"] = null;
}

$amountChoises = getAmountChoices($studentData);
$eventToRoomAssignment = eventToRoomAssignment($roomData, $eventData, $amountChoises["allChoices"]);
$amountEventSpace = [];
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

$organisationsplan = getOrganisationsplan($eventToRoomAssignment, $eventData, $timeslotToTime);
$anwesenheitsliste = getAnwesenheitsliste($assignment, $studentData, $eventData, $timeslotToTime);
$schuelerLaufzettel = getSchuelerLaufzettel($assignment, $timeslotToTime, $studentData, $eventToRoomAssignment, $eventData);


echo "\n Organisationsplan! : \n";
echo json_encode($organisationsplan, JSON_PRETTY_PRINT);
echo "\n";

echo "\n Anwesenheitsliste! : \n";
echo json_encode($anwesenheitsliste, JSON_PRETTY_PRINT);
echo "\n";

echo "\n Schülerlaufzettel! : \n";
echo json_encode($schuelerLaufzettel, JSON_PRETTY_PRINT);
echo "\n";

saveResult($organisationsplan, "organisationsplan");
saveResult($anwesenheitsliste, "anwesenheitsliste");
saveResult($schuelerLaufzettel, "schuelerLaufzettel");

function saveResult(array $data, string $filename)
{
    $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    file_put_contents("results/" . $filename . ".json", $json);
}

function getSchuelerLaufzettel($assignment, $timeslotToTime, $studentData, $eventToRoomAssignment, $eventData)
{

    $result = [];
    foreach ($assignment as $studentID => $timeslotToEvent) {
        foreach ($timeslotToEvent as $timeslot => $eventID) {
            $result[$studentID]["class"] = trim($studentData[$studentID]["class"]);
            $result[$studentID]["lastName"] = trim($studentData[$studentID]["name"]);
            $result[$studentID]["firstName"] = trim($studentData[$studentID]["firstName"]);
            $result[$studentID]["assignments"][$timeslotToTime[$timeslot]]["room"] = getRoomFromEventAndTimeslot($eventToRoomAssignment, $eventID, $timeslot);
            $result[$studentID]["assignments"][$timeslotToTime[$timeslot]]["company"] = trim($eventData[$eventID]["company"]);
            $result[$studentID]["assignments"][$timeslotToTime[$timeslot]]["specialization"] = trim($eventData[$eventID]["specialization"]);
            $result[$studentID]["assignments"][$timeslotToTime[$timeslot]]["eventId"] = (string) $eventID;
            $result[$studentID]["assignments"][$timeslotToTime[$timeslot]]['isWish'] = is_string($eventID);
        }
    }
    return $result;
}

function checkIfWishWasFromStudent($eventID, $studentData)
{
    for ($i = 1; $i <= 6; $i++) {
        foreach ($studentData as $fieldname => $choiseID) {
            if ("choice_" . $i == $fieldname && $eventID == $choiseID) {
                $underscorePosition = strpos($fieldname, '_');
                $numberPart = substr($fieldname, $underscorePosition + 1);
                return $numberPart;
            }
        }
    }
}

function getRoomFromEventAndTimeslot($eventToRoomAssignment, $eventID, $timeslot)
{
    foreach ($eventToRoomAssignment as $roomID => $timeslotToEvent) {
        foreach ($timeslotToEvent as $insideTimeslot => $insideEventID) {
            if ($insideTimeslot == $timeslot && $insideEventID == $eventID) {
                return $roomID;
            }
        }
    }
}

function getAnwesenheitsliste($assignment, $studentData, $eventData, $timeslotToTime)
{
    $result = [];
    foreach ($assignment as $studentID => $timeslotToEvent) {
        foreach ($timeslotToEvent as $timeslot => $assignmentEventID) {

            $result[$assignmentEventID]["company"] = [trim($eventData[$assignmentEventID]["company"])];
            $result[$assignmentEventID]["timeslots"][$timeslotToTime[$timeslot]][] = [
                "class" => $studentData[$studentID]["class"],
                "lastName" => $studentData[$studentID]["name"],
                "firstName" => $studentData[$studentID]["firstName"],
            ];

        }
    }
    return $result;
}

function getOrganisationsplan($eventToRoomAssignment, $eventData, $timeslotToTime)
{
    $result = [];
    foreach ($eventToRoomAssignment as $roomID => $timeslotToEvent) {
        foreach ($timeslotToEvent as $timeslot => $eventID) {
            $result[$eventID]["company"] = trim($eventData[$eventID]["company"]);
            $result[$eventID]["timeslots"][] = [
                'time' => $timeslotToTime[$timeslot],
                'timeSlot' => $timeslot,
                'room' => $roomID,
            ];

        }
    }
    return $result;
}

// TODO: Optimierungsbedarf !
function getAmountChoices($studentData)
{
    $amountChoices = [];
    for ($i = 1; $i <= 6; $i++) {
        foreach ($studentData as $id => $array) {
            $choiceField = "choice_" . $i;

            if (!empty($array[$choiceField])) {
                if (isset($amountChoices[$choiceField][$array[$choiceField]])) {
                    $amountChoices[$choiceField][$array[$choiceField]] += 1;
                } else {
                    $amountChoices[$choiceField][$array[$choiceField]] = 1;
                }
            } else if (isset($amountChoices[$choiceField]["null"])) {
                $amountChoices[$choiceField]["null"] += 1;
            } else {
                $amountChoices[$choiceField]["null"] = 1;
            }
        }
    }

    foreach ($studentData as $array) {
        foreach ($array as $key => $value) {
            if (str_starts_with($key, "choice_")) {
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

function getAvailableTimeSlots($raumZuweisung, $timeslot, $roomID, $requiredSeats): array
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

    $raumZuweisung = [];
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
