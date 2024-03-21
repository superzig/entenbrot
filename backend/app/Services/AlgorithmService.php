<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

/**
 * Class AlgorithmService
 *
 */
class AlgorithmService
{

    private const MAX_CACHE_DURATION = 60 * 60 * 24 * 7; // 1 week

    public function getTimesToTimeslots(): array
    {
        return [
            "A" => "08:45 - 9:30",
            "B" => "09:50 - 10:35",
            "C" => "10:35 - 11:20",
            "D" => "11:40 - 12:25",
            "E" => "12:25 - 13:10",
        ];
    }

    public function getTimeToTimeslot(string $timeslot): ?string
    {
        $timeslotToTime = $this->getTimesToTimeslots();
        return $timeslotToTime[$timeslot] ?? null;
    }

    /**
     * @throws \JsonException
     */
    public function run(array $studentData, array $roomData, array $eventData, ?string $cacheKey = null): array
    {
        try {
            // TODO: ENABLE CACHING AGAIN AFTER TESTING

            // $cachedResult = $this->getCachedData($cacheKey);
            // if ($cachedResult) {
            //     return $cachedResult;
            // }


            $studentData = $this->mapIdAsKey($studentData, 'students');
            $roomData = $this->mapIdAsKey($roomData, 'rooms');
            $eventData = $this->mapIdAsKey($eventData, 'events');

            //Vorbelegung der Zuweisungsslots (Kann später i wo in einen anderen schritt eingebunden werden.)
            $assignment = [];
            foreach ($studentData as $key => $array) {
                $assignment[$key]["A"] = null;
                $assignment[$key]["B"] = null;
                $assignment[$key]["C"] = null;
                $assignment[$key]["D"] = null;
                $assignment[$key]["E"] = null;
            }

            $amountChoises = $this->getAmountChoices($studentData);
            $eventToRoomAssignment = $this->eventToRoomAssignment($roomData, $eventData, $amountChoises["allChoices"]);
            $spaceInEventLeft = [];
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
                        if ($studentDataArrayField === "choice" . $i) {
                            if ($studentDataArray["choice" . $i] != null) {
                                foreach ($spaceInEventLeft[$studentDataArray["choice" . $i]] as $roomID => $eventAssignmentArray) {
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
                        $mostSpaceEventID = $this->findEventWithMostSpaceLeft($spaceInEventLeft, $timeslot, $studentTimeslotsToEventsAssignmentArray);
                        $assignment[$studentID][$timeslot] = $mostSpaceEventID;
                        $spaceInEventLeft[$mostSpaceEventID][array_key_first($spaceInEventLeft[$mostSpaceEventID])][$timeslot]--;
                    }
                }
            }

            $organisationsplan = $this->getOrganisationsplan($eventToRoomAssignment, $eventData);
            $anwesenheitsliste = $this->getAnwesenheitsliste($assignment, $studentData, $eventData);
            $schuelerLaufzettel = $this->getSchuelerLaufzettel($assignment, $studentData, $eventToRoomAssignment, $eventData);

            $erfuellungsScore = $this->getErfuellungsScore($studentData, $schuelerLaufzettel);



            $isError = false;
            if ($cacheKey) {
                ['isError' => $isError, 'cacheKey' => $cacheKey] = $this->store($erfuellungsScore, $studentData, $schuelerLaufzettel, $cacheKey);
            }

            return [
                'cacheKey'   => $cacheKey,
                'isError'    => $isError,
                'cachedTime' => null,
                'data'       => [
                    'erfuellungsscore' => $erfuellungsScore,
                    // 'schuelerLaufzettel'     => $schuelerLaufzettel,
                    'studentData' => $studentData,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'cacheKey'   => null,
                'isError'    => true,
                'cachedTime' => null,
                'error'      => $this->getErrorMessage($e),
                'data'       => [],
            ];
        }
    }

    private function getErfuellungsScore($studentData, $schuelerLaufzettel)
    {
        $totalReachablePoints = null;
        $maxReachablePoints = null;
        $reachedPoints = null;
        foreach ($studentData as $studentID => $studentDataArray) {
            if (!empty($studentDataArray["choice1"])) {
                $maxReachablePoints += 6;
            }
            if (!empty($studentDataArray["choice2"])) {
                $maxReachablePoints += 5;
            }
            if (!empty($studentDataArray["choice3"])) {
                $maxReachablePoints += 4;
            }
            if (!empty($studentDataArray["choice4"])) {
                $maxReachablePoints += 3;
            }
            if (!empty($studentDataArray["choice5"])) {
                $maxReachablePoints += 2;
            }

            if (
                !empty($studentDataArray["choice6"]) && empty($studentDataArray["choice5"])
                || !empty($studentDataArray["choice6"]) && empty($studentDataArray["choice4"])
                || !empty($studentDataArray["choice6"]) && empty($studentDataArray["choice3"])
                || !empty($studentDataArray["choice6"]) && empty($studentDataArray["choice2"])
                || !empty($studentDataArray["choice6"]) && empty($studentDataArray["choice1"])
            ) {
                $maxReachablePoints += 1;
            }
        }

        foreach ($schuelerLaufzettel as $studentID => $studenData) {
            $totalReachablePoints += 20;
            foreach ($studenData["assignments"] as $timeslotData) {

                if (isset($timeslotData["isWish"])) {
                    switch ($timeslotData["isWish"]) {
                        case 1:
                            $reachedPoints += 6;
                            break;
                        case 2:
                            $reachedPoints += 5;
                            break;
                        case 3:
                            $reachedPoints += 4;
                            break;
                        case 4:
                            $reachedPoints += 3;
                            break;
                        case 5:
                            $reachedPoints += 2;
                            break;
                        case 6:
                            $reachedPoints += 1;
                            break;
                    }
                }
            }
        }
        return $result = array("einfach" => round($reachedPoints / $totalReachablePoints, 4) * 100, "wirklich" => round($reachedPoints / $maxReachablePoints, 4) * 100);
    }



    private function mapIdAsKey(array $array, string $type): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            switch ($type) {
                case 'events':
                    $result[$value['eventId']] = $value;
                    break;
                case 'rooms':
                    $result[$value['roomId']] = $value;
                    break;
                default:
                    $result[$key] = $value;
            }
        }
        return $result;
    }

    public function getCachedData(string $cacheKey): ?array
    {
        if (!Storage::disk('algorithm')->exists($cacheKey)) {
            return null;
        }

        return $this->getCachedFilesData($cacheKey);
    }

    private function getErrorMessage(\Exception $e): string
    {
        if (!App::environment('local')) {
            return 'Ein unerwarteter Fehler ist aufgetreten';
        }
        return sprintf("Ein Fehler ist aufgetreten: %s in der Datei %s in Zeile %s", $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * @throws \JsonException
     */
    public function generateUniqueHash(array $fileData1, array $fileData2, array $fileData3): string
    {
        $jsonData1 = json_encode($fileData1, JSON_THROW_ON_ERROR);
        $jsonData2 = json_encode($fileData2, JSON_THROW_ON_ERROR);
        $jsonData3 = json_encode($fileData3, JSON_THROW_ON_ERROR);

        return hash('sha256', $jsonData1 . $jsonData2 . $jsonData3);
    }

    protected function store(array $organisationsplan, array $anwesenheitsliste, array $schuelerLaufzettel, string $cacheKey): array
    {
        $currentTime = time();
        $storedFiles = [
            $this->storageResult([
                'cachedTime'    => $currentTime,
                'maxCachedTime' => $currentTime + self::MAX_CACHE_DURATION,
            ], "metadata", $cacheKey),
            $this->storageResult($organisationsplan, "organizationalPlan", $cacheKey),
            $this->storageResult($anwesenheitsliste, "attendanceList", $cacheKey),
            $this->storageResult($schuelerLaufzettel, "studentSheet", $cacheKey),
        ];

        $storedSuccessfully = count(array_filter($storedFiles, static function (bool $storedSuccessfully) {
            return !$storedSuccessfully;
        })) === 0;

        return [
            'isError'  => !$storedSuccessfully,
            'cacheKey' => $cacheKey,
        ];
    }

    /**
     */
    private function storageResult(array $data, string $filename, string $cacheKey): bool
    {
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);

            if (!$json) {
                return false;
            }

            $file = "$cacheKey/$filename.json";
            return Storage::disk('algorithm')->put($file, $json);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getSchuelerLaufzettel(array $assignment, array $studentData, array $eventToRoomAssignment, array $eventData)
    {

        $result = [];
        foreach ($assignment as $studentID => $timeslotToEvent) {
            foreach ($timeslotToEvent as $timeslot => $eventID) {
                $timePeriod = $this->getTimeToTimeslot($timeslot);
                $result[$studentID]["class"] = trim($studentData[$studentID]["class"]);
                $result[$studentID]["lastName"] = trim($studentData[$studentID]["lastName"]);
                $result[$studentID]["firstName"] = trim($studentData[$studentID]["firstName"]);
                $result[$studentID]["assignments"][$timePeriod]["room"] = $this->getRoomFromEventAndTimeslot($eventToRoomAssignment, $eventID, $timeslot);
                $result[$studentID]["assignments"][$timePeriod]["company"] = trim($eventData[$eventID]["company"]);
                $result[$studentID]["assignments"][$timePeriod]["specialization"] = trim($eventData[$eventID]["specialization"]);
                $result[$studentID]["assignments"][$timePeriod]["eventId"] = (string)$eventID;
                $result[$studentID]["assignments"][$timePeriod]['isWish'] = $this->checkIfWishWasFromStudent($studentID, $eventID, $studentData);
            }
        }
        return $result;
    }

    protected function checkIfWishWasFromStudent(string $studentID, string $eventID, array $studentData)
    {
        for ($i = 1; $i <= 6; $i++) {
            foreach ($studentData[$studentID] as $fieldname => $choiseEventID) {
                if ("choice" . $i == $fieldname && $eventID == $choiseEventID) {
                    return $i;
                }
            }
        }
    }

    protected function getRoomFromEventAndTimeslot($eventToRoomAssignment, $eventID, $timeslot)
    {
        foreach ($eventToRoomAssignment as $roomID => $timeslotToEvent) {
            foreach ($timeslotToEvent as $insideTimeslot => $insideEventID) {
                if ($insideTimeslot == $timeslot && $insideEventID == $eventID) {
                    return $roomID;
                }
            }
        }
    }

    protected function getAnwesenheitsliste(array $assignment, array $studentData, array $eventData): array
    {
        $result = [];
        foreach ($assignment as $studentID => $timeslotToEvent) {
            foreach ($timeslotToEvent as $timeslot => $assignmentEventID) {

                $result[$assignmentEventID]["company"] = trim($eventData[$assignmentEventID]["company"]);
                $result[$assignmentEventID]["timeslots"][$this->getTimeToTimeslot($timeslot)][] = [
                    "class"     => $studentData[$studentID]["class"],
                    "lastName"  => $studentData[$studentID]["lastName"],
                    "firstName" => $studentData[$studentID]["firstName"],
                ];
            }
        }
        return $result;
    }

    protected
    function getOrganisationsplan(array $eventToRoomAssignment, array $eventData): array
    {
        $result = [];
        foreach ($eventToRoomAssignment as $roomID => $timeslotToEvent) {
            foreach ($timeslotToEvent as $timeslot => $eventID) {
                $result[$eventID]["company"] = trim($eventData[$eventID]["company"]);
                $result[$eventID]["timeslots"][] = [
                    'time'     => $this->getTimeToTimeslot($timeslot),
                    'timeSlot' => $timeslot,
                    'room'     => $roomID,
                ];
            }
        }
        return $result;
    }

    // TODO: Optimierungsbedarf !
    protected function getAmountChoices($studentData)
    {
        $amountChoices = [];
        for ($i = 1; $i <= 6; $i++) {
            foreach ($studentData as $id => $array) {
                $choiceField = "choice" . $i;

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
                if (str_starts_with($key, "choice")) {
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

    protected function getAvailableTimeSlots($raumZuweisung, $timeslot, $roomID, $requiredSeats): array
    {
        $availableSlots = [];
        $timeSlots = ['A', 'B', 'C', 'D', 'E'];

        // Finde den Startindex des gegebenen Timeslots
        $startIndex = array_search($timeslot, $timeSlots, true);

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

    protected function anzahlAufeinanderFolgendeSlotsFrei($raumZuweisung, $startTimeslot, $roomID)
    {
        $maxConsecutive = 0;
        $currentConsecutive = 0;
        $timeSlots = ['A', 'B', 'C', 'D', 'E'];

        $startIndex = array_search($startTimeslot, $timeSlots, true);

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

    protected function eventToRoomAssignment($roomData, $eventData, $amountChoisesAll): array
    {

        $raumZuweisung = [];
        uasort($roomData, static function ($a, $b) {
            return $b["capacity"] - $a["capacity"];
        });
        uasort($eventData, static function ($a, $b) {
            return $b['maxParticipants'] - $a['maxParticipants'];
        });

        foreach ($eventData as $eventID => $eventDataArray) {

            $raumFuerEvent = null;

            $teilnehmerAnzahl = $amountChoisesAll[$eventID];
            $maximaleTeilnehmerAnzahlProEvent = $eventDataArray["maxParticipants"];

            foreach ($roomData as $roomID => $roomDataArray) {

                $teilnehmerAnzahlProEvent = ($maximaleTeilnehmerAnzahlProEvent > $teilnehmerAnzahl) ? $teilnehmerAnzahl : $maximaleTeilnehmerAnzahlProEvent;
                $teilnehmerAnzahlProEvent = ($teilnehmerAnzahlProEvent > $roomDataArray["capacity"]) ? $roomDataArray["capacity"] : $teilnehmerAnzahlProEvent;
                $anzahlEventsUmAlleWuenscheZuErfuellen = ceil($teilnehmerAnzahl / $teilnehmerAnzahlProEvent);
                $anzahlEvents = ($anzahlEventsUmAlleWuenscheZuErfuellen > $eventDataArray["amountEvents"]) ? $eventDataArray["amountEvents"] : $anzahlEventsUmAlleWuenscheZuErfuellen;

                if ($this->anzahlAufeinanderFolgendeSlotsFrei($raumZuweisung, $eventDataArray["earliestTimeSlot"], $roomID) >= $anzahlEvents) {
                    $raumFuerEvent = $roomID;
                    break;
                }
            }

            foreach ($this->getAvailableTimeSlots($raumZuweisung, $eventDataArray["earliestTimeSlot"], $raumFuerEvent, $anzahlEvents) as $timeslot) {
                $raumZuweisung[$raumFuerEvent][$timeslot] = $eventID;
            }
        }
        return $raumZuweisung;
    }

    //This function is searching the event with the most avalible places for the given timeslot which is not alrady inside the student assignment.
    protected function findEventWithMostSpaceLeft($spaceInEventLeft, $timeslot, $studentTimeslotsToEventsAssignmentArray)
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


    public function retrieveFullCache()
    {
        $cacheDirectories = Storage::directories('algorithm');

        $data = [];
        foreach ($cacheDirectories as $cacheDirectory) {
            $baseCachedDirectory = pathinfo($cacheDirectory, PATHINFO_BASENAME);
            $data[$baseCachedDirectory] = $this->getCachedFilesData($baseCachedDirectory);
        }

        return $data;
    }

    public function deleteCache(string $cacheKey): bool
    {
        if (!Storage::exists("algorithm/$cacheKey")) {
            return false;
        }
        return Storage::disk('algorithm')->deleteDirectory($cacheKey);
    }

    /**
     * @param string $cacheKey
     *
     * @return array|null
     */
    private function getCachedFilesData(string $cacheKey): ?array
    {
        $files = Storage::files("algorithm/$cacheKey");

        $data = [];
        $maxCachedTime = null;
        foreach ($files as $file) {
            $content = Storage::json($file);
            $basename = pathinfo($file, PATHINFO_FILENAME);
            if ($basename === 'metadata') {
                $maxCachedTime = (int)$content['maxCachedTime'];

                if ($maxCachedTime < time()) {
                    Storage::disk('algorithm')->deleteDirectory($cacheKey);
                    return null;
                }
                continue;
            }

            $data[$basename] = $content;
        }

        return [
            'cacheKey'   => $cacheKey,
            'isError'    => false,
            'cachedTime' => $maxCachedTime,
            'data'       => $data,
        ];
    }
}
