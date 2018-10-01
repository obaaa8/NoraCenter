<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use Avatar;
use Storage;
use CRUDBooster;
use CB;
use DB;

class ApiAccountsController extends Controller
{
  public function userAccounts()
  {
    $result = [];

    $id = Auth::user()->id;

    $results['disscount_values'] = DB::table('groups_trainees')
                ->where('trainees_id',$id)
                ->where('fees_remaining','>',0)
                ->sum('disscount_value');

    $results['fees_remaining'] = DB::table('groups_trainees')
              ->where('trainees_id',$id)
              ->sum('fees_remaining');

    $results['fees_remaining'] = $results['fees_remaining'] - $results['disscount_values'];

    return response()->json($results);
  }

}
