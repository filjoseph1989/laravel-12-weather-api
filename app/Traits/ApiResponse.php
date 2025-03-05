<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $status
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function successResponse($data = null, string $message = '', int $status = 200): \Illuminate\Http\JsonResponse
    {
        $response = ['success' => true];
        if ($message) {
            $response['message'] = $message;
        }
        if ($data !== null) {
            $response['data'] = $data;
        }
        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response.
     * 
     * @param string $message
     * @param string $error
     * @param int $status
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, string $error = '', int $status = 500): \Illuminate\Http\JsonResponse
    {
        $response = ['success' => false, 'message' => $message];
        if ($error) {
            $response['error'] = $error;
        }
        return response()->json($response, $status);
    }
}