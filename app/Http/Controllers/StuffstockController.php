
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StuffstockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

}
