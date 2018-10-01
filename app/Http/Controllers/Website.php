<?php
namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use CRUDBooster;
use NoraCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class Website extends Controller {

  //
  public function index()
  {
    return view('index');
  }
  //
  public function about()
  {
    $data['branches'] = DB::table('branches')->get();
    return view('website.about',$data);
  }
  //
  public function to_connect()
  {
    return view('website.to_connect');
  }
  //
  public function trainers()
  {
    $data['trainers'] = DB::table('cms_users')
                          ->where('cms_users.id_cms_privileges',6)
                          ->orderby('cms_users.id','desc')
                          ->paginate(12);
    return view('website.trainers',$data);
  }
  //
  public function events()
  {
    // $data['page_title'] = 'Home - Blog';
  	// $data['page_description'] = 'This is my simple blog';
  	// $data['blog_name'] = $this->blog_name;
  	// $data['categories'] = DB::table('categories')->get();

  	$data['result'] = DB::table('posts')
  	// ->join('categories','categories.id','=','categories_id')
  	// ->join('cms_users','cms_users.id','=','cms_users_id')
  	// ->select('posts.*','categories.name as name_categories','cms_users.name as name_author')
  	->orderby('posts.id','desc')
    ->paginate(9);
  	// ->take(5)
  	// ->get();
    // dd($data);
  	return view('website.events',$data);
  }
  //
  public function getEvent($event) {

    	$row = DB::table('posts')
    	// ->join('categories','categories.id','=','categories_id')
    	// ->join('cms_users','cms_users.id','=','cms_users_id')
    	// ->select('posts.*','categories.name as name_categories','cms_users.name as name_author')
    	->where('posts.slug',$event)
    	->first();
    	$data['row'] = $row;
    	$data['page_title'] = $row->title.' | MySimpleBlog';
    	$data['page_description'] = str_limit(strip_tags($row->content),155);
    	// $data['blog_name'] = $this->blog_name;
    	// $data['categories'] = DB::table('categories')->get();

    	return view('website.events_view',$data);
    }
  //
	public static function getSpecialtie($specialtie) {
    $data = [];
    $data['specialtie'] = DB::table('specialties')->where('name',$specialtie)->first();
    $data['courses'] = DB::table('courses')
    ->where('specialties_id',$data['specialtie']->id)
    ->orderby('courses.id','desc')
    ->paginate(12);
    // dd($data);
    return view('website.specialtie',$data);
	}

  public function postConnect(Request $request)
  {
    DB::table('contact_forms')->insert([
      'name' => $request->name,
      'email' =>  $request->email,
      'subject' =>  $request->subject,
      'contact_message' =>  $request->contact_message,
    ]);

    CRUDBooster::redirect(url('to_connect'),'تم إرسال رسالتك بنجاح','success');
  }
  public function addContact(Request $request)
  {
    DB::table('contact_forms')->insert([
      'name' => Auth::user()->name,
      'email' =>  Auth::user()->email,
      'subject' =>  $request->subject,
      'contact_message' =>  $request->contact_message,
    ]);
    return response()->json([
        'message' => 'تم إرسال رسالتك بنجاح',
    ], 201);

  }

}
