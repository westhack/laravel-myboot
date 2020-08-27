<?php

namespace App\Modules\Swagger\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class SwaggerController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('swagger::index', ['title' => config('swagger.title', 'Swagger Api')]);
    }
}
