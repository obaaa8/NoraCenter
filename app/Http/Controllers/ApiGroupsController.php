<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Group;
use Avatar;
use Storage;
use CRUDBooster;
use NoraCenter;
use CB;
use DB;
use App\Http\Controllers\Notification;

class ApiGroupsController extends Controller
{
    public function getUserGroups()
    {
      $results = DB::table('groups_trainees')->where('trainees_id',Auth::user()->id)->get();
			$groups_trainee = [];
			foreach ($results as $key => $result) {
				$groups_trainee[$key]['group_name']           = DB::table('groups')->where('id',$result->groups_id)->value('name');
				$groups_trainee[$key]['groups_id']            = $result->groups_id;
				$groups_trainee[$key]['course_name']          = DB::table('courses')->where('id',DB::table('groups')->where('id',$result->groups_id)->value('courses_id'))->value('name');
				$groups_trainee[$key]['fees_paid']            = $result->fees - $result->fees_remaining;
				$groups_trainee[$key]['total_fees_remaining'] = $result->fees_remaining - $result->disscount_value;
				$attendances = DB::table('attendances')->where('groups_id',$result->groups_id)->first();
				$attended = DB::table('attendance_trainees')->where('attendances_id',DB::table('attendances')->where('groups_id',$result->groups_id)->value('id'))->where('trainees_id',$result->trainees_id)->where('status','attended')->get();
				$groups_trainee[$key]['attendances']          = count($attended).' -of- '.$attendances->lectures_number;
				$groups_trainee[$key]['result']               = DB::table('certificates_details')->where('trainees_id',$result->trainees_id)->where('certificates_id',DB::table('certificates')->where('groups_id',$result->groups_id)->value('id'))->value('degree');
				$groups_trainee[$key]['certificates_details_status']   = DB::table('certificates_details')->where('certificates_id',DB::table('certificates')->where('groups_id',$result->groups_id)->value('id'))->where('trainees_id',$result->trainees_id)->value('certificate_status');
				$groups_trainee[$key]['certificates_status']   = DB::table('certificates')->where('groups_id',$result->groups_id)->value('status');
			}
      return response()->json($groups_trainee);
    }
    public function current_groups()
    {
      $results = [];
      $results['count'] = DB::table('groups')
        ->where('status','open')
        ->where('vacant_seats','>=',0)
        ->count();

      $results['groups'] = DB::table('groups')
        ->where('groups.status','open')
        ->where('groups.vacant_seats','>=',0)
        ->get();

      return response()->json($results);
    }
    public function joinToGroup(Request $request)
    {
      $groups_id = $request->groups_id;
      $trainees_id = Auth::user()->id;

      if (DB::table('groups_trainees')->where('groups_id',$groups_id)->where('trainees_id',$trainees_id)->first()) {
        $result = [];
        $result['api_status'] = false;
        $result['api_message'] = 'موجود بالفعل';
        $res = response()->json($result, 401);
        $res->send();
        exit;
      }
      // addTraineesGroup
      $result = NoraCenter::addTraineesGroup($groups_id, $trainees_id);

      if(!$result){
          $result['api_status'] = false;
          $result['api_message'] = 'warning';
          $res = response()->json($result, 401);
          $res->send();
          exit;
        }

      // start: percentage marketing
      $percentage_marketing = DB::table('percentage_marketings')
                              ->where('trainees_id',$trainees_id)
                              ->first();

      $trainee = DB::table('cms_users')->where('id',$trainees_id)->first();
      if (!$percentage_marketing && $trainee->created_by) {

        DB::table('percentage_marketings')->insert([
          'groups_id' => $groups_id,
          'trainees_id' =>  $trainees_id,
          'marketers_id'  =>  $trainee->created_by,
          'created_at'  =>  now()
        ]);

      }
      // end: percentage marketing

      // Notification
      $postdata = [];
      $postdata['id_cms_users'] = $trainees_id;
      $postdata['groups_name'] = DB::table('groups')->where('id',$groups_id)->value('name');
      Notification::startGroupTrainee($postdata);

      // add log
      CRUDBooster::insertLog(trans("notification.logAddGroupTrainee", ['trainee_name'=>$trainee->name,'groups_name'=>$postdata['groups_name']]));

      return response()->json([
          'message' => 'Successfully'
      ], 201);

    }
    public function leftToGroup(Request $request)
    {
      $groups_id = $request->groups_id;
      $trainees_id = Auth::user()->id;

      if (DB::table('groups')->where('id',$groups_id)->where('groups.status','real')->first()) {
        $result = [];
        $result['api_status'] = false;
        $result['api_message'] = 'real group';
        $res = response()->json($result, 401);
        $res->send();
        exit;
      }
      if (! DB::table('groups_trainees')->where('groups_id',$groups_id)->where('trainees_id',$trainees_id)->first()) {
        $result = [];
        $result['api_status'] = false;
        $result['api_message'] = 'غير موجود';
        $res = response()->json($result, 401);
        $res->send();
        exit;
      }
      $result = NoraCenter::deleteTraineesGroup($groups_id, $trainees_id);
      if(!$result){
          $result['api_status'] = false;
          $result['api_message'] = 'warning';
          $res = response()->json($result, 401);
          $res->send();
          exit;
        }
        // Notification
        $postdata = [];
        $postdata['id_cms_users'] = $trainees_id;
        $postdata['groups_name'] = DB::table('groups')->where('id',$groups_id)->value('name');
        Notification::deleteGroupTrainee($postdata);

        // add log
        CRUDBooster::insertLog(trans("notification.logDeleteGroupTrainee", ['trainee_name'=>DB::table('cms_users')->where('id',$trainees_id)->value('name'),'groups_name'=>$postdata['groups_name']]));

        return response()->json([
            'message' => 'Successfully'
        ], 201);
    }
}
