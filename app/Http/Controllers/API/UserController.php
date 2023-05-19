<?php

namespace App\Http\Controllers\API;

use App\Attendee;
use App\Event;
use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function index()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        // $registered = array();
        $attendees = array();
        $events = array();
        $images = array();
        
        try {
            if($user->access_level == 1) { // Village user
                $events = DB::table('events')
                    ->join('users', 'events.user_id', '=', 'users.id')
                    ->where('users.id', $user->id)
                    ->select('users.name', 'users.image', 'events.*')
                    ->get();
                $images = User::find($user->id)->getImages;
                $attendees = (new Attendee)->villageAttendees($user->id, true);
            }elseif($user->access_level == 2) { //Parent user
                $events = DB::table('events')
                    ->join('users', 'events.user_id', '=', 'users.id')
                    ->where('events.date', '>=', Carbon::now()->toDateString())
                    ->select('users.name', 'users.image', 'events.*')
                    ->get();
                foreach ($events as $event) {
                    $images_ = DB::table('images')->where(['event_id' => $event->id])->first();
                    if(isset($images_)){
                        array_push($images, $images_);
                    }
                }
            }
            
            return response()->json([
                'user' => $user,
                'events' => $events,
                'images' => $images,
                'attendees' => $attendees,
                // 'registered' => $registered
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'Token error.'
            ], 500);
        }
    }
    public function fetchKids(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }

        try {
            $kids = User::find($user->id)->getKids;
            return response()->json([
                'kids' => $kids,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'Token error.'
            ], 500);
        }
    }
    public function villageAttendees($id) {
        $attendees = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.village_id' => $id, 'attendees.accepted' => true])
            ->select('attendees.*', 'kids.kid_name', 'kids.photo', 'kids.dob')
        ->get();
        return $attendees;
    }
    public function update(Request $request)
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
