<?php

namespace App\Http\Controllers\API;

use App\Email;
use App\Http\Controllers\Controller;
use App\Mail\PasswordReset;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
// use Tymon\JWTAuth\Facades\JWTAuth;



class ForgotPasswordController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        try {
            $email = $request['email'];
            if($this->validateEmail($email)) {
                // $this->sendMail($email);
                return response()->json([
                    'title' => 'Successful',
                    'email' => $email,
                ], 200);
            }else {
                return response()->json([
                    'title' => 'Error!',
                    'email' => $email,
                ], 404);
            }
            
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
    }
    public function validateEmail($email) {
        $user = User::where('email', $email)->first();
        if(isset($user))
        return true;
        else return false;
    }
    public function encrypt($email) {
        $user = new User();
        $user->email = $email;
        $user->expires = Carbon::now()->addDays(1);
        return Crypt::encryptString($user);
    }
    public function sendMail($email){
        $token = $this->encrypt($email);
        $this->storeToken($token, $email);
        $data = new Email();
        $data->title = 'Reset your Password';
        $data->token = $token;
        $data->email = $email;
        $data->hideme = Carbon::now();
        Mail::to($email)->send(new PasswordReset($data));
    }
    public function storeToken($token, $email){
        $findUser = DB::table('password_resets')->where('email', $email)->first();
        if(isset($isOtherToken)) {
            $findUser->delete();
        }
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()            
        ]);
    }
    public function ResetPassword(Request $request)
    {
        $this->validate($request, [
            'new_password' => 'required|confirmed|min:6',
        ]);
        try {
            $user = $this->decryptToken($request['token']);
            $this->verifyUser($user);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
    }
    public function decryptToken($token) {
        try {
            return Crypt::decryptString($token);
        } catch (DecryptException $e) {
            $this->errorHandler();
        }
    }
    public function errorHandler() {
        return response()->json([
            'title' => 'Error!',
            'message' => 'Your token is invalid. Please try resending a new \'forgot password\' link.'
        ], 401);
    }
    private function verifyUser($this_user){
        $user = DB::table('password_resets')->where([
            'email' => $this_user->email
        ]);
        // $userToken = $this->decryptToken($user->token);
    }
    // private function tokenNotFoundError() {
    //     return response()->json([
    //         'title' => 'Error!',
    //         'message' => 'Your token is invalid. Please try resending a new \'forgot password\' link.'
    //     ], 401);
    // }
    private function doResetPassword($request) {
        $userData = User::whereEmail($request->email)->first();
        $userData->update([
            'password'=>bcrypt($request->password)
        ]);
        $this->updatePasswordRow($request)->delete();
        return response()->json([
            'title' => 'Successful',
            'message' => 'Your password has been reset. Proceed to sign in with your new password.'
        ], 200);
    }
    // public function SignInUser($request) {
    //     $credentials = $request->only('email', 'password');
    //     if( !$token = JWTAuth::attempt($credentials)) {
    //         return '';
    //     }
    //     return $token;
    // }


   
    public function destroy($id)
    {
        //
    }
}
