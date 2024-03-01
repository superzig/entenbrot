<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

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
                'number' => $worksheet->getCell('A' . $row)->getValue(),
                'capacity' => $worksheet->getCell('B' . $row)->getValue(),
            ];
        }

        return new JsonResponse($data);
    }
}

