<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class ExportCsvController extends BaseController
{

    public function index(): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    {
        return view('test');
    }

    public function export(int $testId = null): void
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
}

