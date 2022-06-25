<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\SendForgotPasswordMail;
use App\Traits\ImageTrait;

class AuthController extends Controller
{

    /**
     * validate the login request.
     * @return \Illuminate\Http\Response with auth token
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return response(['status' => 'error', 'message' => 'Validation errors', 'errors'=>$validator->errors()], 422);
        }

        $user = User::where('email',$request->email)->get()->first();
        
        if(!$user || !app('hash')->check($request->password,$user->password)){
            return response(['status'=>'error','message' => 'Invalid credentials!'],401);
        }

        $res = $this->token($user);
        $res['message'] = 'User logged successfully';

        return response($res,200);

    }


    /**
     * register new user.
     * @return \Illuminate\Http\Response with auth token
     */
    public function register(Request $request){

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|regex:/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix|max:255|unique:users',
            'password' => 'required|string|min:8|max:20|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            'image' => 'required|image|max:2048',
        ],[
            'email.regex' => 'Please enter valid email',
            'password.regex' => 'Password must be at least 8 characters and must contain at least one number, uppercase letter, lowercase letter and special characters.'
        ]);

        if($validator->fails()){
            return response(['status' => 'error', 'message' => 'Validation errors', 'errors'=>$validator->errors()], 422);
        }

        /* use trait file upload function  **/
        $image = ImageTrait::uploadFile($request, 'image', 'users');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'image' => $image,
            'password' => app('hash')->make($request->password),
        ]);

        $res = $this->token($user);
        $res['message'] = 'User registered successfully';

        return response($res,201);

    }

    /**
     * unset the user tokens
     */
    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        $res = ['status'=>'success', 'message' => 'Logged out'];
        return response($res,200);
    }


    /**
     * validate the request and send the mail.
     * @return Mail with OTP
     */
    public function forgot(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email'
        ],[
            'email.exists' => 'Enter email id not found in our records'
        ]);
        if($validator->fails()){
            return response(['status'=>'error','message'=>'Validation error','errors'=>$validator->errors()],422);
        }
        $user = User::where('email',$request->email)->get()->first();
        $otp = substr(str_shuffle("123456789ABCDEFGHJKMNPQRSTUVWXYZ"),0,6);
        $user->update(['otp'=>$otp,'otp_created_at'=>Carbon::now()]);

        $details = [
            'name' => $user->name,
            'to' => $user->email,
            'otp' => $otp,
            'subject' => env('APP_NAME').' Forgot Password Notification',
        ];
        try{
            Mail::send(new SendForgotPasswordMail($details));
        }catch(Exception $e){
            return response(['status'=>'error','message'=>'Sending mail error. Please try again..'],422);
        }
        return response(['status'=>'success','message'=>'Successfully sent the OTP. Please check your inbox'],200);

    }

    /**
     * reset the password using OTP
     * @return auth token
     */
    public function reset(Request $request){

        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
            'password' => 'required|string|min:8|max:20|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/'
        ],[
            'email.exists' => 'Enter email id not found in our records',
            'password.regex' => 'Password must be at least 8 characters and must contain at least one number, uppercase letter, lowercase letter and special characters.'
        ]);
        if($validator->fails()){
            return response(['status'=>'error','message'=>'Validation error','errors'=>$validator->errors()],422);
        }
        $user = User::where('email',$request->email)->where('otp',$request->otp)->get()->first();

        if(!$user){ return response(['status'=>'error','message'=>'Invalid OTP'],422); }
        
        $otp_created = new Carbon($user->otp_created_at);
        if(Carbon::now()->greaterThan($otp_created->addMinutes(60))){
            return response(['status'=>'error','message'=>'OTP Expired'],422); 
        }
    
        $user->update([
            'password' => app('hash')->make($request->password),
            'otp' => null,
            'otp_created_at' => null
        ]);

        $res = $this->token($user);
        $res['message'] = 'User password updated successfully';

        return response($res,200);
    }

    /**
     * create token
     * @return auth token
     */
    public function token($user){
        $res['access_token'] = $user->createToken('MyApp')->accessToken;
        $res['token_type'] = 'Bearer';
        $res['user']    = $user;
        $res['status']  = 'success';
        return $res;
    }


}
