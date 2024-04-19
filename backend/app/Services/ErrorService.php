<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\App;

/**
 * Class ErrorService
 * @package App\Services
 *
 * This service is responsible for handling errors and exceptions

 */
class ErrorService
{
    /**
     * Returns the error message of the exception
     * If the app is not in local environment, a generic error message will be returned
     *
     * @param Exception $e
     *
     * @return string
     */
    public static function getErrorMessage(Exception $e): string
    {
        if (!App::environment('local')) {
            return 'Ein unerwarteter Fehler ist aufgetreten';
        }
        return sprintf("Ein Fehler ist aufgetreten: %s in der Datei %s in Zeile %s", $e->getMessage(), $e->getFile(), $e->getLine());
    }
}
