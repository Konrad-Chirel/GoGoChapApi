<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function successResponse($data, $message = 'success', $code = 200)
    {
        return response()->json([
            'data' => $data,
            'message' => $message,
            'code' => $code,
        ], $code);
    }

    protected function errorResponse($message = 'error', $code = 400)
    {
        return response()->json([
            'message' => $message,
            'code' => $code,
        ], $code);
    }
}