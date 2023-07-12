<?php

namespace App\Http\Controllers\API;

use App\Allergy;
use App\Email;
use App\Hobby;
use App\User;
use App\Http\Controllers\Controller;
use App\Illness;
use App\Image;
use App\Kid;
use App\Mail\WelcomeEmail;
use App\Mail\YourAccountIsReady;
use App\Notification;
use App\VillageAccess;
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
            // $newuser->zipcode = $request['zipcode'];
            $newuser->email = $email;
            $newuser->password = bcrypt($request['password']);
            $newuser->save();
            $token = $this->signInUser($request);
            $this->sendMail($email, $name);
            $user_id = $newuser->id;
            $url = null;
            $content = 'We\'re excited to have you on board. We\'ve sent an email to '.$email.', please open your email and click on the \'Verify account\' button to confirm it. If you can\'t find it in your inbox kindly check your spam folder.';
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
            $kid_id = $kid->id;
            if($request['tempImage'] != null) {
                if (Storage::disk('public')->exists($id.'/temp'.'/'.$request['tempImage'])) {
                    Storage::disk('public')->move($id.'/temp'.'/'.$request['tempImage'], $id.'/'.$request['tempImage']);
                    Storage::deleteDirectory('public/'.$id.'/temp');
                };
            }
            foreach ($request['hobbies'] as $key) {
                $this->SaveHobbies($key['name'], $id, $kid_id);
            }
            foreach ($request['illnesses'] as $key) {
                $this->SaveIllnesses($key['name'], $id, $kid_id);
            }foreach ($request['allergies'] as $key) {
                $this->SaveAllergies($key['name'], $id, $kid_id);
            }
            return response()->json([
                'kid' => $kid,
                'hobbies' => $this->GetHobbies($id),
                'illnesses' => $this->GetIllnesses($id),
                'allergies' => $this->GetAllergies($id)
            ], 200); 

        } catch (\Throwable $th) {
            $this->errorMsg();
        }  
    }
    public function SaveHobbies($name, $user_id, $kid_id) {
        $hobby = new Hobby();
        $hobby->name = $name;
        $hobby->user_id = $user_id;
        $hobby->kid_id = $kid_id;
        $hobby->save();
        return $hobby;
    }
    public function SaveIllnesses($name, $user_id, $kid_id) {
        $illness = new Illness();
        $illness->name = $name;
        $illness->user_id = $user_id;
        $illness->kid_id = $kid_id;
        $illness->save();
        return $illness;
    }
    public function SaveAllergies($name, $user_id, $kid_id) {
        $allergy = new Allergy();
        $allergy->name = $name;
        $allergy->user_id = $user_id;
        $allergy->kid_id = $kid_id;
        $allergy->save();
        return $allergy;
    }
    public function GetHobbies($user_id) {
        return User::find($user_id)->getHobbies;
    }
    public function GetIllnesses($user_id) {
        return User::find($user_id)->getIllnesses;
    }
    public function GetAllergies($user_id) {
        return User::find($user_id)->getAllergies;
    }
    public function UpdateKid(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'gender' => 'required',
            'dob' => 'required'
        ]);
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $user_id = $user->id;
        $newImage = $request['tempImage'];
        $kid = Kid::findOrFail($id);
        $oldImage = $kid->photo;
        try {
            $kid->kid_name = $request['name'];
            $kid->gender = $request['gender'];
            $kid->height = $request['height'];
            $kid->dob = $request['dob'];
            $kid->about = $request['about'];
            if($newImage != null) {
                if($newImage == $oldImage) {
                    $this->deleteTemp($id);
                }else {
                    $kid->photo = $newImage;
                    Storage::disk('public')->move($user_id.'/temp'.'/'.$newImage, $user_id.'/'.$newImage);
                    $this->deleteTemp($user_id);
                    $this->deleteOldCopy($user_id, $oldImage);
                }
            } else {
                $kid->photo = null;
                $this->deleteOldCopy($user_id, $oldImage);
            }
            $kid->update();
            $kid_id = $kid->id;
            $Oldhobbies = Kid::find($kid_id)->getHobbies;
            $Oldillnesses = Kid::find($kid_id)->getIllnesses;
            $Oldallergies = Kid::find($kid_id)->getAllergies;
            foreach ($Oldhobbies as $key) {
                $key->delete();
            }
            foreach ($Oldillnesses as $key) {
                $key->delete();
            }
            foreach ($Oldallergies as $key) {
                $key->delete();
            }
            foreach ($request['hobbies'] as $key) {
                $this->SaveHobbies($key['name'], $user_id, $kid_id);
            }
            foreach ($request['illnesses'] as $key) {
                $this->SaveIllnesses($key['name'], $user_id, $kid_id);
            }foreach ($request['allergies'] as $key) {
                $this->SaveAllergies($key['name'], $user_id, $kid_id);
            }

        } catch (\Throwable $th) {
            $this->errorMsg();
        }
        return response()->json([
            'kid' => $kid,
            'hobbies' => $this->GetHobbies($user_id),
            'illnesses' => $this->GetIllnesses($user_id),
            'allergies' => $this->GetAllergies($user_id)
        ], 200);   
    }
    public function deleteTemp($id) {
        Storage::deleteDirectory('public/'.$id.'/temp');
    }
    public function deleteOldCopy($id, $image) {
        if(Storage::disk('public')->exists($id.'/'.$image)) {
            Storage::disk('public')->delete($id.'/'.$image);
        }
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
            $email = $request['email'];
            $village_image = $request['tempImage'];
            $newVillage = new User();
            $newVillage->name = $request['village_name'];
            $newVillage->email = $email;
            $newVillage->phone = $request['phone'];
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
            if($request['sendEmail']) {
            //Send email
                $data = new Email();
                $data->name = $request['village_name'];
                $data->email = $email;
                $data->password = $request['password'];
                $data->account_type = 'village account';
                $data->url = config('hosts.fe');
                Mail::to($email)->send(new YourAccountIsReady($data));
            }
            return response()->json($newVillage, 200);
        } catch (\Throwable $th) {
            $this->errorMsg();
        }
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
    public function DeleteKid($id)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $kid = User::find($user->id)->getKids()
            ->where('id', $id)
            ->first();
        $kid->delete();
        return response()->json($id, 200);
    }
    
}
