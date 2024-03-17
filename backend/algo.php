<?php


$useFakeData = false;

/* FAKE DATA CREATION START */
$specialization = array(
    "Fachinformatiker für Anwendungsentwicklung",
    "Fachinformatiker für Systemintegration",
    "Informationstechnischer Assistent",
    "IT-Systemelektroniker",
    "Mediengestalter Digital und Print",
    "Mathematisch-technischer Softwareentwickler",
    "IT-Systemkaufmann",
    "Elektroniker für Informations- und Systemtechnik",
    "Kaufmann für IT-Systemmanagement",
    "Fachkraft für Lagerlogistik",
    "Informatikkaufmann",
    "IT-Administrator",
    "Netzwerkadministrator",
    "Webentwickler",
    "Anwendungsentwickler",
    "Datenbankadministrator",
    "IT-Projektmanager",
    "IT-Sicherheitsexperte",
    "Softwaretester",
    "Wirtschaftsinformatiker"
);

$timeSlots = array(
    "A" => "08:45 - 09:30",
    "B" => "09:50 - 10:35",
    "C" => "10:35 - 11:20",
    "D" => "11:40 - 12:25",
    "E" => "12:25 - 13:10"
);

function generateClassData($amount)
{
    $classes = array();
    $letters = range('a', 'z');
    $numbers = range(0, 9);

    for ($i = 0; $i < $amount; $i++) {
        do {
            $randomLetters = array_rand($letters, 3);
            $randomNumbers = array_rand($numbers, 3);
            $className =  $letters[$randomLetters[0]] . $letters[$randomLetters[1]] . $letters[$randomLetters[2]] . $numbers[$randomNumbers[0]] . $numbers[$randomNumbers[1]] . $numbers[$randomNumbers[2]];
        } while (isset($classes[$className]));

        $classes[$className] =  $className;
    }
    return $classes;
}

function generateRoomData($amountRooms)
{
    $roomData = array();
    for ($i = 0; $i < $amountRooms; $i++) {
        do {
            $firstDigit = mt_rand(0, 3);
            $roomNumber = $firstDigit . mt_rand(0, 9) . mt_rand(0, 9); // Zufällige 3-stellige Zahl mit erstem Ziffer aus $firstDigit
        } while (array_key_exists($roomNumber, $roomData)); // Prüfen, ob die Raumnummer bereits existiert

        // Zufällige Zahl in 5er-Schritten zwischen 15 und 45 mit einem Minimum von 15
        $capacity = mt_rand(3, 9) * 5;

        $roomData[$roomNumber] = array(
            "room_number" => $roomNumber,
            "capacity" => $capacity
        );
    }
    return $roomData;
}

function generateEventData($amount, $timeSlots, $faker, $specialization)
{
    $eventData = array();

    for ($i = 1; $i < $amount; $i++) {
        $eventData[$i]["eventNumber"] = $i;
        $eventData[$i]["company"] = $faker->company;
        $eventData[$i]["specialization"] = $specialization[array_rand($specialization)];
        $eventData[$i]["maxParticipants"] = mt_rand(3, 9) * 5;
        $eventData[$i]["staringSlot"] = array_rand($timeSlots);
        $eventData[$i]["amountEvents"] = getAmountEventsBasedOnStartingSlot($eventData[$i]["staringSlot"]);
    }

    return $eventData;
}

function getAmountEventsBasedOnStartingSlot($startingSlot)
{
    switch ($startingSlot) {
        case 'A':
            return mt_rand(1, 5);
            break;
        case 'B':
            return mt_rand(1, 4);
            break;
        case 'C':
            return mt_rand(1, 3);
            break;
        case 'D':
            return mt_rand(1, 2);
            break;
        case 'E':
            return 1;
            break;
        default:
            return null;
    }
}

function generateStudentData($amount, $classes, $faker, $events)
{
    $studentData = array();
    for ($i = 1; $i < $amount + 1; $i++) {



        $studentData[$i]["studentNumber"] = $i;
        $studentData[$i]["class"] = $classes[array_rand($classes)];
        $studentData[$i]["lastName"] = $faker->lastname;
        $studentData[$i]["firstName"] = $faker->firstName;

        $shuffledEventsKeys = array_keys($events);
        shuffle($shuffledEventsKeys);

        for ($j = 1; $j <= 6; $j++) {
            $studentData[$i]["choice_" . $j] = mt_rand(1, 9) === 1 ? null : (isset($shuffledEventsKeys[$j - 1]) ? $shuffledEventsKeys[$j - 1] : null);
        }
    }
    return $studentData;
}
/* FAKE DATA CREATION END */

/* DATA CREATION START */ 
function readExcelFile($filePath)
{
    $data = [];

    if (($handle = fopen($filePath, "r")) !== false) {
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

if ($useFakeData) {
    $classesData = generateClassData(7);
    $roomData = generateRoomData(15); //Max 400 cause no more numbers are possible room startst with 0 1 2 or 3 ! 
    $eventData = generateEventData(27, $timeSlots, $faker, $specialization);
    $studentData =  generateStudentData(135, $classesData, $faker, $eventData);
} else {
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
$json = json_encode($spaceInEventLeft, JSON_PRETTY_PRINT);
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
