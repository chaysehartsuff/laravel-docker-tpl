<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Util\AI;
use App\Util\GoogleSearch;
use Illuminate\Support\Facades\Redis;

class SalesForceController extends Controller
{
    public function test(Request $request){

        //var_dump(GoogleSearch::findAvailableParameters());
        
    }
}
