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
    private function generatePresenceList(array $data, ZipArchive $zip)
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
                Storage::disk('local')->put($csvFileName, $csvContent);
                $zip->addFile(storage_path('app/'.$csvFileName), "Anwesenheiten/$csvFileName");
            }
        }
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
    public function generateRunningLog(array $studentsData, ZipArchive $zip)
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
            Storage::disk('local')->put($csvFileName, $csvContent);
            $zip->addFile(storage_path('app/'.$csvFileName), "Laufzettel/$csvFileName");
        }
    }


    public function downloadDocuments($cacheKey)
    {
        if (!$cacheKey) {
            return new JsonResponse(['isError' => true, 'message' => 'No cache key provided', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 400);
        }

        try {
            $files = Storage::files("algorithm/$cacheKey");
            $zipFile = 'Entenbrot-Dokumente.zip'; // Name of the final zip file
            $zip = new ZipArchive;
            if ($zip->open(public_path($zipFile), ZipArchive::CREATE) === true) {
                // Add files to the zip file
                foreach ($files as $file) {
                    if (Storage::exists($file)) {
                        switch (pathinfo($file, PATHINFO_FILENAME)) {
                            case 'attendanceList':
                                $zip->addEmptyDir('Anwesenheiten');
                                $data = Storage::json($file);
                                $this->generatePresenceList($data, $zip);
                                break;
                            case 'studentSheet':
                                $zip->addEmptyDir('Laufzettel');
                                $data = Storage::json($file);
                                $this->generateRunningLog($data, $zip);
                                break;
                            case 'organizationalPlan':
                                $zip->addEmptyDir('Raumplan');
                                $data = Storage::json($file);
                                $this->generateCompanyRoomList($data, $zip);
                                break;
                        }
                    }
                }
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
     * Generiert CSV-Dateien für jedes Unternehmen basierend auf den angegebenen JSON-Daten und packt sie in eine ZIP-Datei.
     *
     * @param array $data
     * @param ZipArchive $zip
     * @return void
     */
    public function generateCompanyRoomList(array $data, ZipArchive $zip): void
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
            Storage::disk('local')->put($csvFileName, $csvContent);
            $zip->addFile(storage_path('app/'.$csvFileName), "Raumplan/$csvFileName");
        }
    }
}

