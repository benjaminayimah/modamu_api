<?php

namespace App\Http\Controllers\API;

use App\Email;
use App\User;
use App\Http\Controllers\Controller;
use App\Image;
use App\Kid;
use App\Mail\WelcomeEmail;
use App\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;

class SignUpController extends Controller
{
    public function signInUser($request)
    {
        $credentials = $request->only('email', 'password');
        if( !$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'Invalid credentials'
            ], 401);
        }
        return $token;
    }
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'password' => 'required|min:6',
        ]);
        try {
            $email = $request['email'];
            $name = $request['name'];
            $newuser = new User();
            $newuser->name = $name;
            $newuser->email = $email;
            $newuser->password = bcrypt($request['password']);
            $newuser->save();
            $token = $this->signInUser($request);
            $this->sendMail($email, $name);
            $user_id = $newuser->id;
            $url = null;
            $content = 'We\'re excited to have you on board. We\'ve sent an email to '.$email.', please open your email and click on the \' Verify account\' button to confirm it. If you can\'t find it in your inbox kindly check your spam folder.';
            $this->sendNotification($user_id, $url, $content);
            return response()->json([
                'name' => $newuser->name,
                'email' => $newuser->email,
                'id' => $newuser->id,
                'state' => 2,
                'heading' => 'Now lets get to know about you',
                'sub_heading' => 'Tell us a bit about yourself',
                'footer' => 'Getting to know you',
                'progress' => 50,
                'remember_token' => $token,
            ], 200);
 
        } catch (\Throwable $th) {
            $this->errorMsg();
        }
    }
    public function sendMail($email, $name){
        $token = Crypt::encryptString($email);
        $host = config('hosts.fe');
        $data = new Email();
        $data->name = $name;
        $data->url = $host.'/'.'new-account-verification/'.$token;
        Mail::to($email)->send(new WelcomeEmail($data));
    }
    public function sendNotification($user_id, $url, $content)
    {
        (new Notification())->insertNotification($user_id, $url, $content);
    }
    public function parentDetails(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required',
            'relationship' => 'required',
        ]);
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $id = $user->id;
            $user = User::findOrFail($id);
            $user->phone = $request['phone'];
            $user->emergency_number = $request['emergency_number'];
            $user->relationship = $request['relationship'];
            $user->ocupation = $request['ocupation'];
            $user->update(); 
        } catch (\Throwable $th) {
            $this->errorMsg();
        }
        return response()->json([
            'state' => 3,
            'heading' => 'Add your children',
            'sub_heading' => 'Now lets get your kids registered',
            'footer' => 'Register your kids',
            'progress' => 75
        ], 200);   
    }
    public function kidDetails(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'gender' => 'required',
            'dob' => 'required'
        ]);
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $id = $user->id;
        try {
            $kid = new Kid();
            $kid->kid_name = $request['name'];
            $kid->photo = $request['tempImage'];
            $kid->gender = $request['gender'];
            $kid->height = $request['height'];
            $kid->dob = $request['dob'];
            $kid->about = $request['about'];
            $kid->user_id = $id;
            $kid->save();
            if($request['tempImage'] != null) {
                if (Storage::disk('public')->exists($id.'/temp'.'/'.$request['tempImage'])) {
                    Storage::disk('public')->move($id.'/temp'.'/'.$request['tempImage'], $id.'/'.$request['tempImage']);
                    Storage::deleteDirectory('public/'.$id.'/temp');
                };
            }

        } catch (\Throwable $th) {
            $this->errorMsg();
        }
        return response()->json([
            'kid' => $kid
        ], 200);   
    }
    public function registerVillage(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'village_name' => 'required',
            'address' => 'required',
            'password' => 'required|min:6',
        ]);
        try {
            $village_image = $request['tempImage'];
            $newVillage = new User();
            $newVillage->name = $request['village_name'];
            $newVillage->email = $request['email'];
            $newVillage->address = $request['address'];
            $newVillage->latitude = $request['latitude'];
            $newVillage->longitude = $request['longitude'];
            $newVillage->password = bcrypt($request['password']);
            $newVillage->access_level = '1';
            $newVillage->save();
            $admin_id = $user->id;
            $village_id = $newVillage->id;
            
            if($village_image != null) {
                $newVillage->image = $village_image;
                $newVillage->update();
                Storage::makeDirectory('public/'.$village_id);
                if (Storage::disk('public')->exists($admin_id.'/temp'.'/'.$village_image)) {
                    Storage::disk('public')->move($admin_id.'/temp'.'/'.$village_image, $village_id.'/'.$village_image);
                    Storage::deleteDirectory('public/'.$admin_id.'/temp');
                };
            }
        } catch (\Throwable $th) {
            $this->errorMsg();
        }
        return response()->json($newVillage, 200);
    }
    public function errorMsg() {
        return response()->json([
            'title' => 'Error!',
            'status' => 'Could not create user.'
        ], 500);
    }

    public function VerifyAccount(Request $request)
    {
        try {
            $email = Crypt::decryptString($request->token);
            $user = User::whereEmail($email)->first();
            if(isset($user)) {
                if(!$user->email_verified) {
                    $user->email_verified = true;
                    $user->update();
                }
                return response()->json([
                    'status' => 'success',
                    'msg' => 'Your account has been verified successfully',
                ], 200);
            }
            return response()->json( $this->NotFound(), 401);
        } catch (DecryptException $e) {
            return response()->json( $this->NotFound(), 401);
        }
    }
    public function NotFound()
    {
        return [
            'status' => 'failed',
            'msg' => 'Sorry we couldn\'t verify your email with the submitted credentials. Click the button below to try again. If the issue persists, please contact support.'
        ];
    }
    
    public function update(Request $request, $id)
    {
        //
    }
    public function destroy($id)
    {
        //
    }
}
