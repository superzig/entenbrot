<?php

namespace App\Http\Controllers;

use Faker\Factory;
use Faker\Generator;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Faker\Generator as Faker;
class ValidateController extends BaseController
{

    public function index(): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    {
        return view('test');
    }

    public function returnCompanies(): JsonResponse
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load(request()->file->path());
        $worksheet = $spreadsheet->getSheet(0);//
        $lastRow = $worksheet->getHighestRow();
        $data = [];
        for ($row = 1; $row <= $lastRow; $row++) {
            $data[] = [
                'number' => $worksheet->getCell('A' . $row)->getValue(),
                'company' => $worksheet->getCell('B' . $row)->getValue(),
                'specialty' => $worksheet->getCell('C' . $row)->getValue(),
                'participants' => $worksheet->getCell('D' . $row)->getValue(),
                'eventMax' => $worksheet->getCell('E' . $row)->getValue(),
                'earliestDate' => $worksheet->getCell('F' . $row)->getValue(),
            ];
        }
        array_shift($data);

        return new JsonResponse($data);
    }

    public function returnStudents(): JsonResponse
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load(request()->file->path());
        $worksheet = $spreadsheet->getSheet(0);//
        $lastRow = $worksheet->getHighestRow();
        $data = [];
        for ($row = 1; $row <= $lastRow; $row++) {
            $data[] = [
                'class' => $worksheet->getCell('A' . $row)->getValue(),
                'firstname' => $worksheet->getCell('B' . $row)->getValue(),
                'lastname' => $worksheet->getCell('C' . $row)->getValue(),
                'choice1' => $worksheet->getCell('D' . $row)->getValue(),
                'choice2' => $worksheet->getCell('E' . $row)->getValue(),
                'choice3' => $worksheet->getCell('F' . $row)->getValue(),
                'choice4' => $worksheet->getCell('G' . $row)->getValue(),
                'choice5' => $worksheet->getCell('H' . $row)->getValue(),
                'choice6' => $worksheet->getCell('I' . $row)->getValue(),
            ];
        }
        array_shift($data);

        return new JsonResponse($data);
    }

    public function returnRooms(): JsonResponse
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load(request()->file->path());
        $worksheet = $spreadsheet->getSheet(0);//
        $lastRow = $worksheet->getHighestRow();
        $data = [];
        for ($row = 1; $row <= $lastRow; $row++) {
            $data[] = [
                'name' => strval($worksheet->getCell('A' . $row)->getValue()),
                'capacity' => $worksheet->getCell('B' . $row)->getValue(),
            ];
        }

        return new JsonResponse($data);
    }


    function generateClassData($amount)
    {
        $classes = array();
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
            $eventData[$i]["maxAmountStudentsPerEvent"] = mt_rand(3, 9) * 5;
            $eventData[$i]["staringSlot"] = array_rand($timeSlots);
            $eventData[$i]["amountEvents"] = $this->getAmountEventsBasedOnStartingSlot($eventData[$i]["staringSlot"]);
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

    function getAmountEventSpaces($eventData)
    {
        $amountEventSpaces = array();
        foreach ($eventData as $eventNumber => $array) {
            $amountEventSpaces[$eventNumber] = $array["maxAmountStudentsPerEvent"] * $array["amountEvents"];
        }
        return $amountEventSpaces;
    }

    function algoConfig()
    {
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

