<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;





class TempUploadController extends Controller
{

    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $id = $user->id;
        try {
            $file = $request->file('image');
            if($file && $id){
                $rawfile = $_FILES['image']["name"];
                $split = explode(".", $rawfile);
                $fileExt = end($split);
                $imgtitle = 'avatar_'.$id;
                $filename = $imgtitle . '_'. rand(1,999999999) . '.'. $fileExt;
                if (!Storage::directories('public/'.$id.'/temp')) {
                    Storage::makeDirectory('public/'.$id.'/temp');
                }
                Storage::disk('public')->put($id.'/temp'.'/'.$filename, File::get($file));
                return response()->json([
                    'image' => $filename,
                ], 200);
            }
        } catch (JWTException $e) {
            return response()->json([
                'msg' => 'Error!'
            ], 500);
        }
    }
    public function setTempUpdate(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $id = $user->id;
        $image = $user->image;
        if (!Storage::directories('public/'.$id.'/temp')) {
            Storage::makeDirectory('public/'.$id.'/temp');
        }
        if (!Storage::disk('public')->exists($id.'/temp'.'/'.$image)) {
            Storage::disk('public')->copy($id.'/'.$image, $id.'/temp'.'/'.$image);
        };
        return response()->json([
            'status' => 'success',
            'image' => $image

        ], 200);

    }
    public function delStoreTemp($user) {
        //delete from folder
        Storage::deleteDirectory('public/'.$user.'/temp');
        return response()->json([
            'status' => 'success',
        ], 200);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
