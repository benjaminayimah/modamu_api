<?php

namespace App\Http\Controllers\API;

use App\User;
use App\Http\Controllers\Controller;
use App\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        $credentials = $request->only('email', 'password');
        try {
            if( !$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'title' => 'Error!',
                    'status' => 'Invalid credentials'
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'Could not create token.'
            ], 500);
        }
        return response()->json([
            'token' => $token
        ], 200);
    }
    public function update(Request $request, $id)
    {
        
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required',
        ]);
        if($user->access_level == 2) {
            $this->validate($request, [
                'phone_number' => 'required',
            ]);
        }
        $id = $user->id;
        $newImage = $request['tempImage'];
        $updateUser = User::findOrFail($id);
        $oldImage = $updateUser->image;
        if($request['email'] != $updateUser->email) {
            $this->validate($request, [
                'email' => 'unique:users'
            ]);
        }
        try {
            $updateUser->name = $request['name'];
            $updateUser->email = $request['email'];
            $updateUser->phone = $request['phone_number'];
            $updateUser->emergency_number = $request['emergency_number'];
            $updateUser->ocupation = $request['ocupation'];

            // $updateUser->address = $request['address'];
            if($newImage != null) {
                if($newImage == $oldImage) {
                    $this->deleteTemp($id);
                }else {
                    $updateUser->image = $newImage;
                    Storage::disk('public')->move($id.'/temp'.'/'.$newImage, $id.'/'.$newImage);
                    $this->deleteTemp($id);
                    $this->deleteOldCopy($id, $oldImage);
                }
            } else {
                $updateUser->image = null;
                $this->deleteOldCopy($id, $oldImage);
            }
            $updateUser->update();
            return response()->json([
                'user' => $updateUser,
            ], 200);
            
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'Token error.'
            ], 500);
        }
    }
    public function deleteTemp($id) {
        Storage::deleteDirectory('public/'.$id.'/temp');
    }
    public function deleteOldCopy($id, $image) {
        if(Storage::disk('public')->exists($id.'/'.$image)) {
            Storage::disk('public')->delete($id.'/'.$image);
        }
    }
    public function changePass(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $this->validate($request, [
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);
        try {
            $user = User::findOrFail($user->id);
            $current_pass = $user->password;
            $new_password = $request['new_password'];
            if (Hash::check($request['current_password'], $current_pass)) {
                $user->password = bcrypt($new_password);
                $user->update();
            }else {
                $new_err = new Error();
                $new_err->current_password = array('The password does not match.');
                return response()->json([
                    'message' => 'Errors',
                    'errors' => $new_err
                ], 422);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!'
            ], 500);
        }
        return response()->json([
            'message' => 'Password is updated'
        ], 200);
    }
    public function destroy()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        auth()->logout();
        return response()->json(['status', 'logged out!'], 200);
    }
}
