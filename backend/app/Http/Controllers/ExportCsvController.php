<?php

namespace App\Http\Controllers;

use App\Services\AlgorithmService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ExportCsvController extends BaseController
{

    public const DIR_ATTENDANCE = 'Anwesenheiten';
    public const DIR_RUNS = 'Laufzettel';
    public const DIR_ROOMS = 'Raumplan';
    protected AlgorithmService $algorithmService;

    public function __construct(AlgorithmService $algorithmService)
    {
        $this->algorithmService = $algorithmService;
    }

    /**
     * Generiert eine CSV-Anwesenheitsliste basierend auf den bereitgestellten Daten und stellt sie zum Download bereit.
     *
     * @param string $cacheKey Der Cache-Schlüssel für die Anwesenheitslisten-Daten.
     *
     * @throws Exception Wenn das Schreiben in die CSV-Datei fehlschlägt.
     */
    private function generatePresenceList(array $data, ZipArchive $zip, string $cacheKey)
    {
        $timeslotsNumbers = array_flip($this->algorithmService->getTimesToTimeslots());
        foreach ($data as $companyId => $companyData) {
            $companyName = $companyData['company'];
            $timeslots = $companyData['timeslots'];

            foreach ($timeslots as $timeslot => $attendees) {
                $slotNumber = $timeslotsNumbers[$timeslot] ?? $timeslot;
                $csvFileName = self::convertToFileName("$companyName-$slotNumber.csv", );
                $csvContent = "Last Name,First Name,Anwesend\n";
                foreach ($attendees as $attendee) {
                    $csvContent .= "{$attendee['lastName']};{$attendee['firstName']};;\n";
                }

                $filePath = "attendance/".$csvFileName;
                self::saveCsvFile($cacheKey, $filePath , $csvContent);
                $zip->addFile(self::getCsvPath($cacheKey, $filePath), self::DIR_ATTENDANCE."/$csvFileName");
            }
        }
    }

    private static function saveCsvFile(string $cacheKey, string $fileName, string $content): void
    {
        $file = "$cacheKey/$fileName";
        Storage::disk('csv')->put($file, $content);
    }

    private static function getCsvPath(string $cacheKey, string $fileName): string
    {
        return storage_path("app/csv/$cacheKey/$fileName");
    }

    private static function convertToFileName(string $file, string $defaultName = 'file.csv'): string
    {
        $replacements = [
            'ä' => 'ae',
            'Ä' => 'Ae',
            'ö' => 'oe',
            'Ö' => 'Oe',
            'ü' => 'ue',
            'Ü' => 'Ue',
            'ß' => 'ss',
        ];

        foreach ($replacements as $search => $replace) {
            $file = str_replace($search, $replace, $file);
        }

        $file = preg_replace('/[^a-zA-Z0-9-_.]/u', '_', $file ?? '');
        $file = preg_replace('/_{2,}/', '_', $file ?? '');

        if (empty($file)) {
            return random_int(0, 10).$defaultName;
        }
        return trim($file, '_');
    }

    /**
     * Generates a CSV running log based on the provided name and an array of room numbers and dates.
     *
     * @param string $name        The name for the running log entry.
     * @param array  $runningData An array containing room numbers and dates for the running log entry.
     *
     * @throws Exception If writing to the CSV file fails.
     */
    public function generateRunningLog(array $studentsData, ZipArchive $zip, string $cacheKey)
    {

        foreach ($studentsData as $student) {
            $lastName = $student['lastName'];
            $firstName = $student['firstName'];
            $assignments = $student['assignments'];

            $csvFileName = self::convertToFileName("$firstName-$lastName.csv");
            $csvContent = "Time Slot;Room;Company;Specialization;\n";
            foreach ($assignments as $timeSlot => $assignment) {
                $room = $assignment['room'];
                $company = $assignment['company'];
                $specialization = $assignment['specialization'];
                $csvContent .= "$timeSlot;$room;$company;$specialization;\n";
            }
            $filePath = "runs/".$csvFileName;
            self::saveCsvFile($cacheKey, $filePath , $csvContent);
            $zip->addFile(self::getCsvPath($cacheKey, $filePath), self::DIR_RUNS."/$csvFileName");
        }
    }

    /**
     * Generiert CSV-Dateien für jedes Unternehmen basierend auf den angegebenen JSON-Daten und packt sie in eine ZIP-Datei.
     *
     * @param array $data
     * @param ZipArchive $zip
     * @return void
     */
    public function generateCompanyRoomList(array $data, ZipArchive $zip, string $cacheKey): void
    {
        foreach ($data as $companyId => $company) {
            $companyName = $company['company'];
            $csvFileName = self::convertToFileName("$companyName-timeslots.csv");
            $csvContent = "Time;Time Slot;Room\n";
            foreach ($company['timeslots'] as $timeslot) {
                $time = $timeslot['time'];
                $timeSlot = $timeslot['timeSlot'];
                $room = $timeslot['room'];
                $csvContent .= "$time;$timeSlot;$room\n";
            }

            $filePath = "rooms/".$csvFileName;
            self::saveCsvFile($cacheKey, $filePath , $csvContent);
            $zip->addFile(self::getCsvPath($cacheKey, $filePath), self::DIR_ROOMS."/$csvFileName");
        }
    }


    public function downloadDocuments($cacheKey)
    {
        if (!$cacheKey) {
            return new JsonResponse(['isError' => true, 'message' => 'No cache key provided', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 400);
        }

        try {
            $zipFile = 'Entenbrot-Dokumente.zip'; // Name of the final zip file
            $zip = new ZipArchive;
            if ($zip->open(public_path($zipFile), ZipArchive::CREATE) === true) {
                // Add files to the zip file
                $this->addDocuments($cacheKey, $zip);
                $zip->close();
                return response()->download(public_path($zipFile))->deleteFileAfterSend(true);

            }
        } catch (Exception $e) {
            return new JsonResponse(['isError' => true, 'message' => $this->getErrorMessage($e) , 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 500);
        }

        return new JsonResponse(['isError' => true, 'message' => 'Error creating zip file', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 500);
    }

    private function getErrorMessage(Exception $e): string
    {
        return $e->getMessage()." in ".$e->getFile()." on line ".$e->getLine();
    }



    /**
     * @param $cacheKey
     * @param ZipArchive $zip
     * @return void
     * @throws Exception
     */
    public function addDocuments(string $cacheKey, ZipArchive $zip): void
    {
        $files = Storage::files("algorithm/$cacheKey");
        foreach ($files as $file) {
            if (Storage::exists($file)) {
                switch (pathinfo($file, PATHINFO_FILENAME)) {
                    case 'attendanceList':
                        $zip->addEmptyDir(self::DIR_ATTENDANCE);
                        $data = Storage::json($file);
                        $this->generatePresenceList($data, $zip, $cacheKey);
                        break;
                    case 'studentSheet':
                        $zip->addEmptyDir(self::DIR_RUNS);
                        $data = Storage::json($file);
                        $this->generateRunningLog($data, $zip, $cacheKey);
                        break;
                    case 'organizationalPlan':
                        $zip->addEmptyDir(self::DIR_ROOMS);
                        $data = Storage::json($file);
                        $this->generateCompanyRoomList($data, $zip, $cacheKey);
                        break;
                }
            }
        }
    }
}

