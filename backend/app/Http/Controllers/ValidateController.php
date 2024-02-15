<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class ValidateController extends BaseController
{

    public function index(): JsonResponse
    {
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

