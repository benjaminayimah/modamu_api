<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notification;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;


class NotificationController extends Controller
{
    
    public function store(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        if($request->read == 1) {
            $notifications = User::find($user->id)->getNotifications;
            foreach ($notifications as $key) {
                $key->read = true;
                $key->update();
            }
        }else {
            $notifications = User::find($user->id)->getNotifications;
        }
        return response()->json($notifications, 200);
    }
    
    public function destroy($id)
    {

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $notification = Notification::findOrFail($id);
        $notification->delete();
        return response()->json($id, 200);
    }
}
