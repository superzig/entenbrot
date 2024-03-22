<?php

namespace App\Http\Controllers;

use App\Services\AlgorithmService;
use Exception;
use http\Env\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportCsvController extends BaseController
{

    protected AlgorithmService $algorithmService;

    public function __construct(AlgorithmService $algorithmService)
    {
        $this->algorithmService = $algorithmService;
    }

    public function index(): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    {
        return view('test');
    }

    /**
     * Exports data to a CSV file and initiates its download.
     *
     * @throws Exception If an error occurs during the CSV file creation or download process.
     */
    public function export(): void
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
                    fputcsv($fh, [strval($dataColumn[0]) => strval($row[0]), strval($dataColumn[1]) => strval($row[1]), strval($dataColumn[2]) => strval($row[2]),  strval($dataColumn[3]) => strval($row[3]),], ";", "\"");
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
    public function generatePresenceList(string $cacheKey): StreamedResponse|null
    {
        // $algoService = new AlgorithmService(); // Diese Zeile entfernen
        // Überprüfe, ob Daten im Cache vorhanden sind
        if (empty($this->algorithmService->getCachedData($cacheKey))) {
            return;
        }

        // Laden der Anwesenheitsliste aus dem Storage
        $data = Storage::json("algorithm/{$cacheKey}/attendanceList.json");

        $zip = new ZipArchive();
        $zipFileName = 'output.zip';
        if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
            exit("Cannot open $zipFileName");
        }

        foreach ($data as $companyId => $companyData) {
            $companyName = $companyData['company'];
            $timeslots = $companyData['timeslots'];

            foreach ($timeslots as $timeslot => $attendees) {
                $csvFileName = "$companyName-$timeslot.csv";
                $csvContent = "Last Name,First Name,Anwesend\n";
                foreach ($attendees as $attendee) {
                    $csvContent .= "{$attendee['lastName']};{$attendee['firstName']};;\n";
                }
                file_put_contents($csvFileName, $csvContent);
                $zip->addFile($csvFileName);
            }
        }

        $zip->close();

        // Download the ZIP file
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=$zipFileName");
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile("$zipFileName");
        // Clean up the generated CSV files
        foreach ($data as $companyId => $companyData) {
            $timeslots = $companyData['timeslots'];
            foreach ($timeslots as $timeslot => $attendees) {
                $csvFileName = "$companyName-$timeslot.csv";
                unlink($csvFileName);
            }
        }
        // Delete the zip file
        unlink($zipFileName);
    }

    /**
     * Generates a CSV running log based on the provided name and an array of room numbers and dates.
     *
     * @param string $name The name for the running log entry.
     * @param array $runningData An array containing room numbers and dates for the running log entry.
     *
     * @throws Exception If writing to the CSV file fails.
     */
    public function generateRunningLog(string $cacheKey): void
    {
        if (empty($this->algorithmService->getCachedData($cacheKey))) {
            return;
        }

        // Laden der Anwesenheitsliste aus dem Storage
        $studentsData = Storage::json("algorithm/{$cacheKey}/studentSheet.json");
        $zip = new ZipArchive();
        $zipFileName = 'output.zip';
        if ($zip->open($zipFileName, ZipArchive::CREATE) !== TRUE) {
            exit("Cannot open $zipFileName");
        }

        foreach ($studentsData as $student) {
            $lastName = $student['lastName'];
            $firstName = $student['firstName'];
            $assignments = $student['assignments'];

            $csvFileName = "$lastName-$firstName-assignments.csv";
            $csvContent = "Time Slot;Room;Company;Specialization;\n";
            foreach ($assignments as $timeSlot => $assignment) {
                $room = $assignment['room'];
                $company = $assignment['company'];
                $specialization = $assignment['specialization'];
                $csvContent .= "$timeSlot;$room;$company;$specialization;\n";
            }
            file_put_contents($csvFileName, $csvContent);
            $zip->addFile($csvFileName);

            $zip->close();

            // Download the ZIP file
            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename=$zipFileName");
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile("$zipFileName");
            // Clean up the generated CSV files
            foreach ($studentsData as $student) {
                $lastName = $student['lastName'];
                $firstName = $student['firstName'];
                $csvFileName = "$lastName-$firstName-assignments.csv";
                unlink($csvFileName);
            }
            // Delete the zip file
            unlink($zipFileName);
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

        if ($zip->open(public_path($zipFile), ZipArchive::CREATE) === TRUE) {
            // Add files to the zip file
            foreach($files as $file) {
                if (Storage::exists($file)) {
                    $zip->addFile(storage_path('app/' . $file), basename($file));
                }
            }

            $zip->close();
        }


        return response()->download(public_path($zipFile))->deleteFileAfterSend(true);

    }


}

