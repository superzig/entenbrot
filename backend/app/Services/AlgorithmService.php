<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Storage;
use JsonException;

/**
 * Class AlgorithmService
 * @package App\Services
 *
 * This service is responsible for running the algorithm and handling the caching of the results
 */
class AlgorithmService
{

    /**
     * Maximum cache duration in seconds
     * default: 60 * 60 * 24 * 7 (1 week)
     */
    private const MAX_CACHE_DURATION = 60 * 60 * 24 * 7;


    /**
     * Returns the mapping of timeslots to times
     *
     * @return string[]
     */
    public function getTimesToTimeslots(): array
    {
        return [
            "A" => "08:45 - 09:30",
            "B" => "09:50 - 10:35",
            "C" => "10:35 - 11:20",
            "D" => "11:40 - 12:25",
            "E" => "12:25 - 13:10",
        ];
    }

    /**
     * Returns the time for a given timeslot
     *
     * @param string $timeslot
     *
     * @return string|null
     */
    public function getTimeToTimeslot(string $timeslot): ?string
    {
        $timeslotToTime = $this->getTimesToTimeslots();
        return $timeslotToTime[$timeslot] ?? null;
    }

    /**
     * Runs the algorithm with the provided data
     *
     * @param array       $studentData
     * @param array       $roomData
     * @param array       $eventData
     * @param string|null $cacheKey (optional) - if provided, the result will be cached
     *
     * @return array
     */
    public function run(array $studentData, array $roomData, array $eventData, ?string $cacheKey = null): array
    {
        try {
             $cachedResult = $this->getCachedData($cacheKey);
             if ($cachedResult) {
                 return $cachedResult;
             }


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
                ['isError' => $isError, 'cacheKey' => $cacheKey] = $this->store($erfuellungsScore, $organisationsplan, $anwesenheitsliste, $schuelerLaufzettel, $cacheKey);
            }

            return [
                'cacheKey'   => $cacheKey,
                'isError'    => $isError,
                'cachedTime' => null,
                'data'       => [
                    'score' => $erfuellungsScore,
                    'organizationalPlan' => $organisationsplan,
                    'attendanceList'     => $anwesenheitsliste,
                    'studentSheet'       => $schuelerLaufzettel,
                ],
            ];
        } catch (Exception $e) {
            return [
                'cacheKey'   => null,
                'isError'    => true,
                'cachedTime' => null,
                'error'      => ErrorService::getErrorMessage($e),
                'data'       => [],
            ];
        }
    }


    /**
     * Calculates the fulfillment score of the students based on their choices and the assigned events
     *
     * @param $studentData
     * @param $schuelerLaufzettel
     *
     * @return int[]|null[]
     */
    private function getErfuellungsScore($studentData, $schuelerLaufzettel)
    {
        $maxReachablePoints = null;
        $totalReachablePoints = null;
        $reachedPoints = null;
        foreach ($studentData as $student) {
            if (!empty($student["choice1"])) {
                $totalReachablePoints += 6;
            }
            if (!empty($student["choice2"])) {
                $totalReachablePoints += 5;
            }
            if (!empty($student["choice3"])) {
                $totalReachablePoints += 4;
            }
            if (!empty($student["choice4"])) {
                $totalReachablePoints += 3;
            }
            if (!empty($student["choice5"])) {
                $totalReachablePoints += 2;
            }

            // Check if the student has a 6th choice and if one of the previous choices is empty
            if (
                (!empty($studentDataArray["choice6"]) && empty($studentDataArray["choice5"]))
                || (!empty($studentDataArray["choice6"]) && empty($studentDataArray["choice4"]))
                || (!empty($studentDataArray["choice6"]) && empty($studentDataArray["choice3"]))
                || (!empty($studentDataArray["choice6"]) && empty($studentDataArray["choice2"]))
                || (!empty($studentDataArray["choice6"]) && empty($studentDataArray["choice1"]))
            ) {
                ++$totalReachablePoints;
            }
        }

        foreach ($schuelerLaufzettel as $student) {
            $maxReachablePoints += 20;
            foreach ($student["assignments"] as $timeslotData) {

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
                            ++$reachedPoints;
                            break;
                    }
                }
            }
        }

        return [
            'reachedPoints' => $reachedPoints,
            'maxReachablePoints' => $maxReachablePoints,
            'totalReachablePoints' => $totalReachablePoints,
        ];
    }

    /**
     * Maps the id of the array as key
     * If the type is 'events' or 'rooms', the key will be the eventId or roomId
     * Otherwise the key will be the index of the array
     * This is used to access the data more easily
     *
     * @param array  $array
     * @param string $type
     *
     * @return array
     */
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

    /**
     * Returns the cached data if it exists
     * Otherwise returns null
     *
     * @param string $cacheKey
     *
     * @return array|null
     */
    public function getCachedData(string $cacheKey): ?array
    {
        if (!Storage::disk('algorithm')->exists($cacheKey)) {
            return null;
        }

        return $this->getCachedFilesData($cacheKey);
    }

    /**
     * Generates a unique hash based on the provided data
     * This hash is used as the cache key
     *
     * @throws JsonException
     */
    public function generateUniqueHash(array $fileData1, array $fileData2, array $fileData3): string
    {
        $jsonData1 = json_encode($fileData1, JSON_THROW_ON_ERROR);
        $jsonData2 = json_encode($fileData2, JSON_THROW_ON_ERROR);
        $jsonData3 = json_encode($fileData3, JSON_THROW_ON_ERROR);

        return hash('sha256', $jsonData1 . $jsonData2 . $jsonData3);
    }


    /**
     * Stores the results in the cache
     *
     * @param array  $erfuellungscore
     * @param array  $organisationsplan
     * @param array  $anwesenheitsliste
     * @param array  $schuelerLaufzettel
     * @param string $cacheKey
     *
     * @return array
     */
    protected function store(array $erfuellungscore, array $organisationsplan, array $anwesenheitsliste, array $schuelerLaufzettel, string $cacheKey): array
    {
        $currentTime = time();
        $storedFiles = [
            $this->storageResult([
                'cachedTime'    => $currentTime,
                'maxCachedTime' => $currentTime + self::MAX_CACHE_DURATION,
            ], "metadata", $cacheKey),
            $this->storageResult($erfuellungscore, "score", $cacheKey),
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
     * Stores the result in the cache
     *
     * @param array  $data
     * @param string $filename
     * @param string $cacheKey
     *
     * @return bool
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
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Returns the running log of the students
     *
     * @param array $assignment
     * @param array $studentData
     * @param array $eventToRoomAssignment
     * @param array $eventData
     *
     * @return array
     */
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

    /**
     * Checks if the wish was from the student
     * If the wish was from the student, the choice number will be returned
     * Otherwise null will be returned
     *
     * @param string $studentID
     * @param string $eventID
     * @param array  $studentData
     *
     * @return int
     */
    protected function checkIfWishWasFromStudent(string $studentID, string $eventID, array $studentData)
    {
        for ($i = 1; $i <= 6; $i++) {
            foreach ($studentData[$studentID] as $fieldname => $choiseEventID) {
                if ("choice" . $i == $fieldname && $eventID == $choiseEventID) {
                    return $i;
                }
            }
        }
        return null;
    }

    /**
     * Returns the room from the event and timeslot
     * If the room is not found, null will be returned
     *
     * @param $eventToRoomAssignment
     * @param $eventID
     * @param $timeslot
     *
     * @return int|string|void
     */
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

    /**
     * Returns the attendance list of the students
     *
     * @param array $assignment
     * @param array $studentData
     * @param array $eventData
     *
     * @return array
     */
    protected function getAnwesenheitsliste(array $assignment, array $studentData, array $eventData): array
    {
        $result = [];
        foreach ($assignment as $studentID => $timeslotToEvent) {
            foreach ($timeslotToEvent as $timeslot => $assignmentEventID) {

                $result[$assignmentEventID]["company"] = trim($eventData[$assignmentEventID]["company"]);
                $result[$assignmentEventID]["specialization"] = trim($eventData[$assignmentEventID]["specialization"]);
                $result[$assignmentEventID]["timeslots"][$this->getTimeToTimeslot($timeslot)][] = [
                    "class"     => $studentData[$studentID]["class"],
                    "lastName"  => $studentData[$studentID]["lastName"],
                    "firstName" => $studentData[$studentID]["firstName"],
                ];
            }
        }
        return $result;
    }

    /**
     * Returns the organizational plan of the events
     *
     * @param array $eventToRoomAssignment
     * @param array $eventData
     *
     * @return array
     */
    protected function getOrganisationsplan(array $eventToRoomAssignment, array $eventData): array
    {
        $result = [];
        foreach ($eventToRoomAssignment as $roomID => $timeslotToEvent) {
            foreach ($timeslotToEvent as $timeslot => $eventID) {
                $result[$eventID]["company"] = trim($eventData[$eventID]["company"]);
                $result[$eventID]["specialization"] = trim($eventData[$eventID]["specialization"]);
                $result[$eventID]["timeslots"][] = [
                    'time'     => $this->getTimeToTimeslot($timeslot),
                    'timeSlot' => $timeslot,
                    'room'     => $roomID,
                ];
            }
        }
        return $result;
    }

    /**
     * Returns the amount of choices for each event
     * @param $studentData
     *
     * @return array
     */
    protected function getAmountChoices($studentData)
    : array
    {
        $amountChoices = [];

        // Initialize 'allChoices' key to handle empty values and prepare for counting
        $amountChoices['allChoices'] = ['null' => 0];

        foreach ($studentData as $id => $student) {
            for ($i = 1; $i <= 6; $i++) {
                $choiceField = "choice$i";
                $choiceValue = $student[$choiceField] ?? 'null'; // Use null coalescing operator for simplicity

                // Increment the choice count for this specific choice
                if (!isset($amountChoices[$choiceField][$choiceValue])) {
                    $amountChoices[$choiceField][$choiceValue] = 0; // Initialize if not set
                }
                ++$amountChoices[$choiceField][$choiceValue];

                // Increment the total choice count if not null, or increment 'null' count otherwise
                if ($choiceValue !== 'null') {
                    if (!isset($amountChoices['allChoices'][$choiceValue])) {
                        $amountChoices['allChoices'][$choiceValue] = 0; // Initialize if not set
                    }
                    ++$amountChoices['allChoices'][$choiceValue];
                } else {
                    ++$amountChoices['allChoices']['null'];
                }
            }
        }

        // If there were no null 'allChoices', remove the 'null' key to clean up
        if ($amountChoices['allChoices']['null'] === 0) {
            unset($amountChoices['allChoices']['null']);
        }

        return $amountChoices;
    }

    /**
     * Returns the available timeslots for the given room and timeslot
     * If the required amount of seats is not available, an empty array will be returned
     * Otherwise the available timeslots will be returned
     *
     * @param $raumZuweisung
     * @param $timeslot
     * @param $roomID
     * @param $requiredSeats
     *
     * @return array
     */
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

    /**
     * Returns the number of consecutive slots available
     *
     * @param $raumZuweisung
     * @param $startTimeslot
     * @param $roomID
     *
     * @return int|mixed
     */
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

    /**
     * Returns the room assignment for the events
     *
     * @param $roomData
     * @param $eventData
     * @param $amountChoisesAll
     *
     * @return array
     */
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

    /**
     * Returns the event with the most space left
     *
     * @param $spaceInEventLeft
     * @param $timeslot
     * @param $studentTimeslotsToEventsAssignmentArray
     *
     * @return int|string|null
     */
    protected function findEventWithMostSpaceLeft($spaceInEventLeft, $timeslot, $studentTimeslotsToEventsAssignmentArray)
    : int|string|null
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


    /**
     * Retrieves all cached data
     * If no cache is found, an empty array will be returned
     *
     * @return array
     */
    public function retrieveFullCache()
    : array
    {
        $cacheDirectories = Storage::directories('algorithm');

        $data = [];
        foreach ($cacheDirectories as $cacheDirectory) {
            $baseCachedDirectory = pathinfo($cacheDirectory, PATHINFO_BASENAME);
            $data[$baseCachedDirectory] = $this->getCachedFilesData($baseCachedDirectory);
        }

        return $data;
    }

    /**
     * Deletes the cache by cacheKey
     * Returns true if the cache was deleted successfully
     *
     * @param string $cacheKey
     *
     * @return bool
     */
    public function deleteCache(string $cacheKey): bool
    {
        if (!Storage::exists("algorithm/$cacheKey")) {
            return false;
        }

        $results = [
            Storage::disk('algorithm')->deleteDirectory($cacheKey),
            Storage::disk('csv')->deleteDirectory($cacheKey),
        ];
        return array_filter($results, static function (bool $result) {
            return !$result;
        }) === [];
    }

    /**
     * Returns the cached data by cacheKey
     * If no cache is found, null will be returned
     *
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
