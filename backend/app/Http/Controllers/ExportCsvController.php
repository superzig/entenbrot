<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Routing\Controller as BaseController;
use ZipArchive;

class ExportCsvController extends BaseController
{

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
     * Generates a CSV attendance list based on the provided data.
     *
     * @param array $data An array of names for the attendance list.
     *
     * @throws Exception If writing to the CSV file fails.
     */
    public function generatePresenceList(array $data): void
    {
        try {
            // Create CSV file
            $timestamp = date('Y-m-d_H-i-s');
            $csvFileName = "attendance_list_{$timestamp}.csv";
            $csvFile = @fopen($csvFileName, 'w+');

            // Check if opening the file was successful
            if ($csvFile === false) {
                throw new \Exception("Error creating the CSV file: {$csvFileName}");
            }

            // Write header
            fputcsv($csvFile, ['Name', 'Anwesend'], ';', "\"");

            // Write data
            foreach ($data as $entry) {
                // Write row to the CSV file, leave presence empty
                if (!is_string($entry) || empty($entry)) {
                    throw new Exception('Invalid format in data array.');
                }

                fputcsv($csvFile, [$entry, null], ';', "\"");
            }

            // Close the file
            fclose($csvFile);

            // Set HTTP headers for download
            header('Content-Type: text/csv');
            header("Content-Disposition: attachment; filename=\"$csvFileName\"");

            // Output & delete the file
            readfile($csvFileName);
            unlink($csvFileName);
            exit();
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * Generates a CSV running log based on the provided name and an array of room numbers and dates.
     *
     * @param string $name The name for the running log entry.
     * @param array $runningData An array containing room numbers and dates for the running log entry.
     *
     * @return string The filename of the generated CSV file.
     * @throws Exception If writing to the CSV file fails.
     */
    public function generateRunningLog(string $name, array $runningData): string
    {
        try {
            // Validate input
            if (empty($name) || empty($runningData)) {
                throw new \Exception("Name and running data must be provided.");
            }

            // Create CSV file
            $tempName = str_replace(' ', '_', $name);
            $csvFileName = "laufzettel_{$tempName}.csv";
            $csvFile = @fopen($csvFileName, 'w+');

            // Check if opening the file was successful
            if ($csvFile === false) {
                throw new Exception("Error creating the CSV file: {$csvFileName}");
            }

            // Write header
            fputcsv($csvFile, ['Raumnummer', 'Datum'], ';', "\"");

            // Sort entries by date (assuming the date is in the second column)
            usort($runningData, function($a, $b) {
                return strtotime($a[1]) - strtotime($b[1]);
            });

            // Write sorted data
            foreach ($runningData as $entry) {
                // Validate each entry in the running data array
                if (!is_array($entry) || count($entry) !== 2 || !is_numeric($entry[0]) || !strtotime($entry[1])) {
                    throw new Exception('Invalid format in running data array.');
                }

                // Write row to the CSV file
                fputcsv($csvFile, $entry, ';', "\"");
            }

            // Close the file
            fclose($csvFile);

            return $csvFileName;
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    /**
     * Generates running logs for each entry in the provided array and creates a ZIP archive for download.
     *
     * @param array $logEntries An array containing entries with names and corresponding running data.
     *
     * @throws Exception If an error occurs during the generation of running logs or creating the ZIP archive.
     */
    public function generateRunningLogsForArray(array $logEntries): void
    {
        try {
            // Validate input
            if (empty($logEntries)) {
                throw new \Exception("Log entries array must not be empty.");
            }

            // Create a temporary directory for storing individual running logs
            $tempDir = 'temp_logs_' . uniqid();
            if (!mkdir($tempDir) && !is_dir($tempDir)) {
                throw new \Exception("Error creating temporary directory.");
            }

            // Generate running logs for each entry in the array
            foreach ($logEntries as $entry) {
                // Validate each entry in the log entries array
                if (
                    !is_array($entry) ||
                    count($entry) !== 2 ||
                    !is_string($entry[0]) ||
                    !is_array($entry[1]) ||
                    empty($entry[0]) ||
                    empty($entry[1])
                ) {
                    throw new \Exception("Invalid format in log entries array.");
                }

                $name = $entry[0];
                $runningData = $entry[1];

                // Generate running log for the current entry
                $csvFileName = $this->generateRunningLog($name, $runningData);

                // Move the generated CSV file to the temporary directory
                rename($csvFileName, "{$tempDir}/laufzettel_{$name}.csv");
            }

            // Create a ZIP archive of the generated running logs
            $timestamp = date('Y-m-d_H-i-s');
            $zipFileName = "running_logs_{$timestamp}.zip";
            $zip = new ZipArchive();
            if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Error creating ZIP archive.");
            }

            // Add all CSV files from the temporary directory to the ZIP archive
            $files = glob("{$tempDir}/*.csv");
            foreach ($files as $file) {
                $zip->addFile($file, basename($file));
            }

            $zip->close();

            // Set HTTP headers for download
            header('Content-Type: application/zip');
            header("Content-Disposition: attachment; filename=\"$zipFileName\"");

            // Output the ZIP archive
            readfile($zipFileName);

            // Delete the temporary directory and ZIP archive
            array_map('unlink', glob("{$tempDir}/*"));
            rmdir($tempDir);
            unlink($zipFileName);
            exit();
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }


}

