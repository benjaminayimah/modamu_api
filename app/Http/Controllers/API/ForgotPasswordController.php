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
                $token = $this->storeToken($email);
                $this->sendMail($token, $email);
                return response()->json([
                    'title' => 'Successful',
                    'email' => $token,
                ], 200);
            }else {
                return response()->json([
                    'title' => 'Error!',
                    'email' => $email,
                ], 404);
            }
            
        } catch (\Throwable $th) {
            return response()->json([
                'title' => $th,
            ], 500);
        }
    }
    public function validateEmail($email) {
        $user = User::where('email', $email)->first();
        if(isset($user))
        return true;
        else return false;
    }
    public function storeToken($email){
        $token = Crypt::encryptString($email);
        $findUser = DB::table('password_resets')->where('email', $email);
        if(isset($findUser)) {
            $findUser->delete();
        }
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()            
        ]);
        return $token;
    }
    public function sendMail($token, $email){
        $host = config('hosts.fe');
        $data = new Email();
        $data->url = $host.'/'.'reset-password/'.$token;
        $data->hideme = Carbon::now();
        Mail::to($email)->send(new PasswordReset($data));
    }

    //Reset----------

    public function ResetPassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|confirmed|min:6',
        ]);
        $email = $this->decryptToken($request['token']);
        return $email ? $this->doResetPassword($email, $request['password']) : $this->tokenException();
    }
    public function decryptToken ($token) {
        try {
            $email = Crypt::decryptString($token);
            if($email) {
                $validated = DB::table('password_resets')->where('email', $email)->first();
                if(isset($validated)) {
                    return $email;
                }else {
                    return false;
                }
            }
        } catch (DecryptException $e) {
            return false;
        }
    }
    public function tokenException() {
        return response()->json([
            'title' => 'Error!',
            'message' => 'Your token is invalid. Please try resending a new \'forgot password\' link.'
        ], 401);
    }
    private function doResetPassword($email, $password) {
        $userData = User::whereEmail($email)->first();
        $userData->update([
            'password' => bcrypt($password)
        ]);
        DB::table('password_resets')->where('email', $email)->delete();
        return response()->json($email, 200);
    }

    public function destroy($id)
    {
        //
    }
}
