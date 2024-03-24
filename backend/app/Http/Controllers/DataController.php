<?php

namespace App\Http\Controllers;

use App\Services\AlgorithmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;

/**
 * Class DataController
 * @package App\Http\Controllers
 *
 * This controller is responsible for handling requests related to data manipulation and algorithm execution
 */
class DataController extends BaseController
{
    protected AlgorithmService $algorithmService;

    public function __construct(AlgorithmService $algorithmService)
    {
        $this->algorithmService = $algorithmService;
    }

    /**
     *  Run algorithm with provided data
     *  If staticData is present in request, data will be loaded from storage
     *  Otherwise, data will be loaded from request
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function algorithmAction(Request $request): JsonResponse
    {
        try {
            [$students, $rooms, $events] = $this->getRequestData($request);

            if (!$students || !$rooms || !$events) {
                return new JsonResponse(['isError' => true, 'message' => 'Missing data', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 400);
            }
            $cacheKey = $this->algorithmService->generateUniqueHash($events, $students, $rooms);

            $result = $this->algorithmService->run($students, $rooms, $events, $cacheKey);
            return new JsonResponse($result, ($result['isError'] ? 500 : 200));
        } catch (\Exception $e) {
            return new JsonResponse(['isError' => true, 'message' => $e->getMessage(), 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 500);
        }
    }

    /**
     *  List all cached data
     *  If no cache found, return empty array
     *
     * @return JsonResponse
     */
    public function listAction(): JsonResponse
    {
        $result = $this->algorithmService->retrieveFullCache();

        return new JsonResponse($result);
    }

    /**
     *  Delete cache by cacheKey
     *  If cache not found, return 404
     *
     * @param $cacheKey
     *
     * @return JsonResponse
     */
    public function deleteAction($cacheKey): JsonResponse
    {
        $success = $this->algorithmService->deleteCache($cacheKey);
        return new JsonResponse(['isError' => !$success, 'message' => $success ? 'Successfully deleted' : 'Cache not found'], ($success ? 200 : 404));
    }

    /**
     * Get algorithm data from cache by cacheKey
     * If cache not found, return 404
     *
     * @param $cacheKey
     *
     * @return JsonResponse
     */
    public function viewAction($cacheKey): JsonResponse
    {
        $result = $this->algorithmService->getCachedData($cacheKey);

        if (!$result) {
            return new JsonResponse(['isError' => true, 'message' => 'Cache not found', 'data' => [], 'cachedTime' => null, 'cacheKey' => null], 404);
        }
        return new JsonResponse($result);
    }

    /**
     * Remove old cache
     * @return JsonResponse
     */
    public function removeOldCacheAction(): JsonResponse
    {
        $hasRemovedCache = $this->algorithmService->removeOldCache();

        if (!$hasRemovedCache) {
            return new JsonResponse(['success' => false, 'message' => 'Keine Auswertungen wurden entfernt.'], 200);
        }
        return new JsonResponse(['success' => true, 'message' => 'Alte Auswertungen wurden erfolgreich entfernt.'], 200);
    }


    /**
     *  Get data from request and return it as array of students, rooms and events
     *  If staticData is present in request, data will be loaded from storage
     *  Otherwise, data will be loaded from request
     *  If data is missing, return null
     *
     * @throws \JsonException
     */
    private function getRequestData($request)
    : array
    {
        if ($request->has('staticData')) {
            $students = Storage::json('students.json');
            $rooms = Storage::json('rooms.json');
            $events = Storage::json('events.json');
        } else {
            $studentsData = $request->input('students');
            $roomsData = $request->input('rooms');
            $eventsData = $request->input('events');
            if (!$studentsData || !$roomsData || !$eventsData) {
                return [null, null, null];
            }

            $students = json_decode($studentsData, true, 512, JSON_THROW_ON_ERROR);
            $rooms = json_decode($roomsData, true, 512, JSON_THROW_ON_ERROR);
            $events = json_decode($eventsData, true, 512, JSON_THROW_ON_ERROR);
        }



        return [$students, $rooms, $events];
    }
}

