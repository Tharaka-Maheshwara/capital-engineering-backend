<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return response()->json(['data' => []]);
    }

    public function show($id)
    {
        return response()->json(['data' => ['id' => $id]]);
    }
}
