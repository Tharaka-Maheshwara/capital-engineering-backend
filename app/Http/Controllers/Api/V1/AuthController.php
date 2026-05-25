<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        return response()->json(['message' => 'register endpoint']);
    }

    public function login(Request $request)
    {
        return response()->json(['message' => 'login endpoint']);
    }
}
