<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

/*
|--------------------------------------------------------------------------
| BaseController
|--------------------------------------------------------------------------
| This controller is the parent for all API controllers.
| It provides standard methods for API responses (success & error).
*/
class BaseController extends Controller
{
    /**
     * Send a standard success response
     *
     * @param mixed $result Data to send in response
     * @param string $message Message to send
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message)
    {
        // Prepare response array
        $response = [
            'success' => true,    // Indicates success
            'data'    => $result, // Response data
            'message' => $message,// Response message
        ];

        // Return JSON response with HTTP status 200
        return response()->json($response, 200);
    }

    /**
     * Send a standard error response
     *
     * @param string $error Error message
     * @param array $errorMessages Optional additional error data
     * @param int $code HTTP status code (default 404)
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        // Prepare response array
        $response = [
            'success' => false, // Indicates failure
            'message' => $error // Main error message
        ];

        // If additional error messages exist, add them
        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }

        // Return JSON response with the specified HTTP code
        return response()->json($response, $code);
    }
}
