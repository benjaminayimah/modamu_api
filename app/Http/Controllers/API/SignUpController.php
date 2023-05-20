<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Http\Controllers\Controller;
use App\Kid;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

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
            $newuser = new User();
            $newuser->name = $request['name'];
            $newuser->email = $request['email'];
            $newuser->password = bcrypt($request['password']);
            $newuser->save();
            $token = $this->signInUser($request);
 
        } catch (\Throwable $th) {
            $this->errorMsg();
        }
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
    }
    // public function progressResponse() {
        
    // }
    public function parentDetails(Request $request)
    {
        $this->validate($request, [
            'emergency_number' => 'required',
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
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'village_name' => 'required',
            'address' => 'required',
            'password' => 'required|min:6',
        ]);
        try {
            $newVillage = new User();
            $newVillage->name = $request['village_name'];
            $newVillage->email = $request['email'];
            $newVillage->address = $request['address'];
            $newVillage->latitude = $request['latitude'];
            $newVillage->longitude = $request['longitude'];
            $newVillage->password = bcrypt($request['password']);
            $newVillage->access_level = '1';
            $newVillage->save();
            $token = $this->signInUser($request);
        } catch (\Throwable $th) {
            $this->errorMsg();
        }
        return response()->json([
            'token' => $token,
        ], 200);
    }
    public function errorMsg() {
        return response()->json([
            'title' => 'Error!',
            'status' => 'Could not create user.'
        ], 500);
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
