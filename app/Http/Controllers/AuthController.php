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

class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(Request $request)
    {

        $request->validate([
            'name' => 'required|string|min:3|max:70',
            'photo' => 'image|max:5120',
            'email' => 'required|email|unique:cms_users',
            'phone_number' => 'required|numeric|min:10|unique:cms_users',
            'gender' => 'required',
            'specialization' => 'required',
            'educational_level' => 'required',
            'address' => 'required|string',
            'password' => 'required',
        ]);



        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'photo' => $request->photo,
            'phone_number' => $request->phone_number,
            'gender' => $request->gender,
            'specialization' => $request->specialization,
            'educational_level' => $request->educational_level,
            'address' => $request->address,
            'id_cms_privileges' => 7,
            'password' => bcrypt($request->password)
        ]);

        $user->save();
        $user_id = DB::table('cms_users')->where('email',$request->email)->value('id');
        if (! $request->hasFile('photo')) {
          $avatar = Avatar::create($user->name)->getImageObject()->encode('png');

          $directory = 'uploads/'.$user_id.'/'.date('Y-m');
          Storage::makeDirectory($directory);

          Storage::put($directory.'/avatar.png', (string) $avatar);
          // Storage::put('avatars/'.$user->id.'/avatar.png', (string) $avatar);
          // $user->notify(new SignupActivate($user));
          $photo =  $directory.'/avatar.png';
        }
        if ($request->hasFile('photo')) {

            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension();

            //Create Directory Monthly
            $directory = 'uploads/'.$user_id.'/'.date('Y-m');
            Storage::makeDirectory($directory);

            //Move file to storage
            $filename = $request->name.'.'.$ext;
            $storeFile = Storage::putFileAs($directory, $file, $filename);
            if ($storeFile) {
                $content = $directory.'/'.$filename;
                $photo = $content;
            }
        }
        DB::table('cms_users')->where('email',$request->email)->update(['photo'=>$photo]);
        // $uesr->photo = $photo;
        // $user->save();

        CRUDBooster::insertLog(trans("crudbooster.log_register", ['email' => $user->email, 'ip' => $request->server('REMOTE_ADDR')]),'',$user_id);

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
            // 'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
      unset($request->user()['id_cms_privileges']);
      unset($request->user()['created_by']);
      unset($request->user()['money']);
      unset($request->user()['age']);
        return response()->json($request->user());
    }
}
