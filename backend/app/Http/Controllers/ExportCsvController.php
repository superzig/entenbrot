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

    public function index()
    : \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    {
        return view('test');
    }

    /**
     * Exports data to a CSV file and initiates its download.
     *
     * @throws Exception If an error occurs during the CSV file creation or download process.
     */
    public function export()
    : void
    {
        $dataColumn = [
            0 => '',
            1 => '8:00',
            2 => '8:45',
            3 => '9:50',
        ];

        $dataRow = [
            '101' => [
                101, 'Polizei', 'Babor', 'Soptim',
            ],
            '102' => [
                102, 'Nobis', 'Sparkasse', 'Babor',
            ],
            '103' => [
                103, 'Aix', 'BMW', 'Zoll',
            ],
        ];


        if ($tmp = tempnam(sys_get_temp_dir(), "export.")) {
            if ($fh = fopen($tmp, "w")) {
                fputs($fh, chr(239) . chr(187) . chr(191));
                //csv heading names
                fputcsv($fh, [strval($dataColumn[0]), strval($dataColumn[1]), strval($dataColumn[2]), strval($dataColumn[3])], ";", "\"");

                //csv column names
                foreach ($dataRow as $row) {
                    fputcsv($fh, [strval($dataColumn[0]) => strval($row[0]), strval($dataColumn[1]) => strval($row[1]), strval($dataColumn[2]) => strval($row[2]), strval($dataColumn[3]) => strval($row[3]),], ";", "\"");
                }

                fclose($fh);
                header("Content-Type: text/csv;charset=utf-8");
                header("Content-Disposition: attachment; filename=\"export-" . date("Y-m-d-H-i") . ".csv\"");
                readfile($tmp);
                unlink($tmp);

                exit();
            }
        }
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
        foreach ($data as $companyId => $companyData) {
            $companyName = $companyData['company'];
            $timeslots = $companyData['timeslots'];

            foreach ($timeslots as $timeslot => $attendees) {
                $csvFileName = "$companyName-$timeslot.csv";
                $csvContent = "Last Name,First Name,Anwesend\n";
                foreach ($attendees as $attendee) {
                    $csvContent .= "{$attendee['lastName']};{$attendee['firstName']};;\n";
                }
                Storage::disk('local')->put($csvFileName, $csvContent);
                $zip->addFile(storage_path('app/'.$csvFileName), "Anwesenheiten/$csvFileName");
            }
        }
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

            $csvFileName = "$firstName-$lastName.csv";
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

        $files = Storage::files("algorithm/$cacheKey");
        $zipFile = 'download.zip'; // Name of the final zip file
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
                        case 'organizationPlan':
                            $zip->addEmptyDir('organizationPlan');

                            break;
                    }
                }
            }
            $zip->close();
            return response()->download(public_path($zipFile))->deleteFileAfterSend(true);

        }

        return new JsonResponse(['isError' => true, 'message' => 'Error creating zip file', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 500);
    }
}

