<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Reply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class UsersController extends Controller
{
    use Reply ;

    protected $from_email = 'ramandeep@yopmail.com';
    protected $from_name = 'Ramandeep Singh';

 
    public function sendSignUpMail(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|unique:users,email'
        ],
        [
            'email.unique' => 'User is already registered.'
        ]);
        if ($validator->errors()->all())        {     return $this->failed($validator->errors()->first());     }

        $sendmail = $this->send_mail('signup',$request->email , $this->from_email,$this->from_name);
        return $sendmail == 202 ? $this->success('Sign-up Email Sent') : $this->failed('Unable to send mail at this moment'); 
    }

    public function BaseRegister(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|unique:users,email,Null,id,is_active,1',
            'user_name' => 'required|string|unique:users,user_name,Null,id,is_active,1|min:4|max:20',
            'password' => 'required|min:6'
        ]);
        if ($validator->errors()->all())        {     return $this->failed($validator->errors()->first());     }

        $otp = rand(100000,999999);

        User::updateOrCreate([
            'email'=> $request->email,
        ],[
            'user_name' => $request->user_name,
            'password' => $request->password,
            'registered_otp' => $otp
        ]);

       $sendmail = $this->send_mail('otp',$request->email , $this->from_email,$this->from_name , $otp);
       return $sendmail == 202 ? $this->success('Otp sent on your email') : $this->failed('Unable to send otp on mail at this moment'); 
    }

    public function FinalRegister(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|exists:users,registered_otp,email,'.$request->email
        ],[
            'otp.exists' => 'Invalid OTP'
        ]);
        if ($validator->errors()->all())        {     return $this->failed($validator->errors()->first());     }

        $user = User::where('email',$request->email);
       if($user->first()->is_active == 1)
       {
           return $this->failed('User is Already Verified!');
       }

       # Update User
       $update = $user->update([
           'is_active' => '1',
           'registered_at' => now()
       ]);

       return $update ? $this->success('User Register & Verified Successfully') : $this->failed('Unable to register at this moment!');
    }

    public function Update(Request $request,$id)
    {
        $model = User::Find($id);
        if(!$model)   {   return $this->failed('Invalid Data');    }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:20',
            'user_role' => 'required|in:admin,user',
            'avatar'    => 'required|dimensions:min_width=256,min_height=256|image'
        ]);
        if ($validator->errors()->all()) {
            return $this->failed($validator->errors()->first());
        }
       
        #Update User Data
        $update = $model->Update([
            'name' => $request->name,
            'user_role' => $request->user_role,
            'avatar' =>  $this->saveImage($request,'avatar')
        ]);
        return $update ? $this->success('User Updated Successfully') : $this->failed('Unable to update at this moment!');
    
    }

    // *************************************************//
    //                --- AUTH  ---                     //
    // *************************************************//

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required|min:6'
        ]);
        if ($validator->errors()->all()) {
            return $this->failed($validator->errors()->first());
        }

        if(!Auth::attempt(['email' => $request->email, 'password' => $request->password]))
        {
            return $this->failed('Invalid Credentails');
        }

        $ss = [
            "grant_type" => "password",
            "client_id" => "2",
            "client_secret" => env('PASSPORT_CLIENT_PASSWORD_KEY',"P5vTlAYhWZ0W5PnWejxIU8ikpvNSyfKaSiNp2imC"),
            "username" => $request->email, //"admin@yopmail.com",
            "password" => $request->password, //"12345678"
        ];

        $request = Request::create('oauth/token', 'POST', $ss);
        $token_response =  app()->handle($request);
        $responseBody = json_decode($token_response->getContent(), true);

        if(!array_key_exists('token_type',$responseBody))
        {
            return $this->failed('Unable to login at this moment');
        }

        return $this->success('Login Successfully' , [
            'user' => Auth::user(),
            'token' => $responseBody
        ]);
    }

    public function logout()
    {
        $token_id = Auth::User()->token()->id;

        // Delete Refresh Token
        DB::table('oauth_refresh_tokens')->where('access_token_id',$token_id)->delete();

        // Delete Refresh Token
        DB::table('oauth_access_tokens')->where('id',$token_id)->delete();

        return $this->success('Logout Successfully');
    }


}
