<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;

class ValidateController extends BaseController
{

    public function index(): JsonResponse
    {

        $contents = File::get(base_path('bot.json'));
        $rooms = File::get(base_path('bo3.json'));

        $collection = collect(json_decode($contents, true));
        $roomContent = collect(json_decode($rooms, true));
        $data = collect($collection);
        $roomData = collect($roomContent);
        $roomData->pop();
        $roomContent->first();
        $allData = [];

        foreach ($collection as $c) {
            foreach ($roomData as $rd) {
                if ($c['Unternehmen'] === $rd['Unternehmen']) {
                    $allData[] = array_merge($c, $rd);
                }
            }
        }

        $timeslot1 = '8:45 – 9:30';
        $timeslot2 = '9:50 – 10:35';
        $timeslot3 = '10:35 – 11:20';
        $timeslot4 = '11:40– 12:25';
        $timeslot5 = '12:25 – 13:10';

        $ts1 = [];
        $ts2 = [];
        $ts3 = [];
        $ts4 = [];
        $ts5 = [];

        foreach ($allData as $ad){
            if ($ad['8:45 – 9:30'] !== ""){
                $ts1[] = $ad;
            }
            if ($ad['9:50 – 10:35'] !== ""){
                $ts2[] = $ad;
            }
            if ($ad['10:35 – 11:20'] !== ""){
                $ts3[] = $ad;
            }
            if ($ad['11:40– 12:25'] !== ""){
                $ts4[] = $ad;
            }
            if ($ad['12:25 – 13:10'] !== ""){
                $ts5[] = $ad;
            }
        }



        dd($ts1, $ts2, $ts3, $ts4, $ts5);
        $json = json_decode(json: $contents, associative: true);

        $data = [
            1,
            2,
            3,
        ];

        return $this->validateToJson($data);
    }

    public function validateToJson(mixed $data): JsonResponse
    {
        return new JsonResponse($data);
    }

}

