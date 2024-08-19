<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Linhas;
use Illuminate\Http\Request;

class LinhasController extends Controller
{
    public function index()
    {
        $data = Linhas::all();
        return response()->json($data);
    }
}
