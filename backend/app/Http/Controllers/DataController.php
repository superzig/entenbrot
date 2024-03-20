<?php

namespace App\Http\Controllers;

use App\Services\AlgorithmService;
use Faker\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class DataController extends BaseController
{
    protected AlgorithmService $algorithmService;

    public function __construct(AlgorithmService $algorithmService)
    {
        $this->algorithmService = $algorithmService;
    }

    /**
     * @throws \JsonException
     */
    public function algorithmAction(Request $request): JsonResponse
    {
        try {
            $studentsData = $request->input('students');
            $roomsData = $request->input('rooms');
            $eventsData = $request->input('events');

            if (!$studentsData || !$roomsData || !$eventsData) {
                return new JsonResponse(['isError' => true, 'message' => 'Missing data', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 400);
            }
            $students = json_decode($studentsData, true, 512, JSON_THROW_ON_ERROR);
            $rooms = json_decode($roomsData, true, 512, JSON_THROW_ON_ERROR);
            $events = json_decode($eventsData, true, 512, JSON_THROW_ON_ERROR);
            $cacheKey = $this->algorithmService->generateUniqueHash($events, $students, $rooms);

            $result = $this->algorithmService->run($students, $rooms, $events, $cacheKey);
            return new JsonResponse($result, ($result['isError'] ? 500 : 200));
        } catch (\Exception $e) {
            return new JsonResponse(['isError' => true, 'message' => $e->getMessage(), 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 500);
        }
    }

    public function listAction(): JsonResponse
    {
        $result = $this->algorithmService->retrieveFullCache();

        return new JsonResponse($result);
    }

    /**
     */
    public function deleteAction($cacheKey): JsonResponse
    {
        $success = $this->algorithmService->deleteCache($cacheKey);
        return new JsonResponse(['isError' => !$success, 'message' => $success ? 'Successfully deleted' : 'Cache not found'], ($success ? 200 : 404));
    }

    /**
     */
    public function viewAction($cacheKey): JsonResponse
    {
        $result = $this->algorithmService->getCachedData($cacheKey);

        if (!$result) {
            return new JsonResponse(['isError' => true, 'message' => 'Cache not found', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 404);
        }
        return new JsonResponse($result);
    }


    function generateClassData($amount): array
    {
        $classes = [];
        $letters = range('a', 'z');
        $numbers = range(0, 9);

        for ($i = 0; $i < $amount; $i++) {
            do {
                $randomLetters = array_rand($letters, 3);
                $randomNumbers = array_rand($numbers, 3);
                $className = $letters[$randomLetters[0]] . $letters[$randomLetters[1]] . $letters[$randomLetters[2]] . $numbers[$randomNumbers[0]] . $numbers[$randomNumbers[1]] . $numbers[$randomNumbers[2]];
            } while (isset($classes[$className]));

            $classes[$className] = $className;
        }
        return $classes;
    }

    function generateRoomData($amountRooms): array
    {
        $roomData = [];
        for ($i = 0; $i < $amountRooms; $i++) {
            do {
                $firstDigit = mt_rand(0, 3);
                $roomNumber = $firstDigit . mt_rand(0, 9) . mt_rand(0, 9); // Zufällige 3-stellige Zahl mit erstem Ziffer aus $firstDigit
            } while (array_key_exists($roomNumber, $roomData)); // Prüfen, ob die Raumnummer bereits existiert

            // Zufällige Zahl in 5er-Schritten zwischen 15 und 45 mit einem Minimum von 15
            $capacity = mt_rand(3, 9) * 5;

            $roomData[$roomNumber] = [
                "room_number" => $roomNumber,
                "capacity" => $capacity,
            ];
        }
        return $roomData;
    }

    function generateEventData($amount, $timeSlots, $faker, $specialization): array
    {
        $eventData = [];

        for ($i = 1; $i < $amount; $i++) {
            $eventData[$i]["eventNumber"] = $i;
            $eventData[$i]["company"] = $faker->company;
            $eventData[$i]["specialization"] = $specialization[array_rand($specialization)];
            $eventData[$i]["maxAmountStudentsPerEvent"] = mt_rand(3, 9) * 5;
            $eventData[$i]["staringSlot"] = array_rand($timeSlots);
            $eventData[$i]["amountEvents"] = $this->getAmountEventsBasedOnStartingSlot($eventData[$i]["staringSlot"]);
        }

        return $eventData;
    }

    /**
     * @throws RandomException
     */
    function getAmountEventsBasedOnStartingSlot($startingSlot): ?int
    {
        switch ($startingSlot) {
            case 'A':
                return random_int(1, 5);
            case 'B':
                return random_int(1, 4);
            case 'C':
                return random_int(1, 3);
            case 'D':
                return random_int(1, 2);
            case 'E':
                return 1;
            default:
                return null;
        }
    }

    public function generateStudentData($amount, $classes, $faker, $events): array
    {
        $studentData = [];
        for ($i = 1; $i < $amount + 1; $i++) {


            $studentData[$i]["studentNumber"] = $i;
            $studentData[$i]["class"] = $classes[array_rand($classes)];
            $studentData[$i]["lastName"] = $faker->lastName;
            $studentData[$i]["firstName"] = $faker->firstName;

            $shuffledEventsKeys = array_keys($events);
            shuffle($shuffledEventsKeys);

            for ($j = 1; $j <= 6; $j++) {
                $studentData[$i]["choice_" . $j] = mt_rand(1, 6) === 1 ? null : ($shuffledEventsKeys[$j - 1] ?? null);
            }

        }
        return $studentData;
    }


    function getAmountChoices($studentData): array
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
                    }
                }
            }
        }
        return $amountChoices;
    }

    function getAmountEventSpaces($eventData): array
    {
        $amountEventSpaces = [];
        foreach ($eventData as $eventNumber => $array) {
            $amountEventSpaces[$eventNumber] = $array["maxAmountStudentsPerEvent"] * $array["amountEvents"];
        }
        return $amountEventSpaces;
    }

    function algoConfig()
    {
        $specialization = [
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
            "Wirtschaftsinformatiker",
        ];

        $timeSlots = [
            "A" => "08:45 - 09:30",
            "B" => "09:50 - 10:35",
            "C" => "10:35 - 11:20",
            "D" => "11:40 - 12:25",
            "E" => "12:25 - 13:10",
        ];

        $faker = Factory::create();
        $classesData = $this->generateClassData(7);
        $roomData = $this->generateRoomData(15); //Max 400 cause no more numbers are possible room startst with 0 1 2 or 3 !
        $eventData = $this->generateEventData(27, $timeSlots, $faker, $specialization);
        $studentData = $this->generateStudentData(136, $classesData, $faker, $eventData);

        $json = json_encode($roomData);
        dump($json);

        $json = json_encode($eventData);
        dump($json);

        $json = json_encode($studentData);
        dump($json);

        $amountChoises = $this->getAmountChoices($studentData);
        $json = json_encode($amountChoises);
        dump($json);

        $amountEventSpaces = $this->getAmountEventSpaces($eventData);
        $json = json_encode($amountEventSpaces);
        dump($json);
        dd('end');
    }
}

