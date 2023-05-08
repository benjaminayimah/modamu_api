<?php

namespace App\Http\Controllers\API;

use App\Booking;
use App\Event;
use App\Http\Controllers\Controller;
use App\Kid;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function store(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $attendees = array();
        $registered = array();
        $events = array();
        $images = array();
        
        try {
            if($user->access_level == 1) {
                $events = User::find($user->id)->getEvents;
                $images = User::find($user->id)->getImages;
            }elseif($user->access_level == 2) {
                $events_ = DB::table('events')
                ->where('date', '=', Carbon::now()->toDateString())
                // ->where('end_time', '>', Carbon::now()->toTimeString())
                // ->where('start_time', '<', Carbon::now()->toTimeString())
                ->get();
                foreach ($events_ as $event) {
                    $images_ = DB::table('images')->where(['event_id' => $event->id])->first();
                    if(isset($images_)){
                        array_push($images, $images_);
                    }
                    $village = DB::table('users')->where('id', $event->user_id)->first();
                    $newEvent = new Event();
                    $newEvent->event = $event;
                    $newEvent->village = $village->name;
                    if(isset($newEvent)){
                        array_push($events, $newEvent);
                    }
                }
                $attendees = User::find($user->id)->getAttendees;
                $registered_ = User::find($user->id)->getBookings;
                foreach ($registered_ as $booking) {
                    $reg_event = DB::table('events')->where('id', $booking->event_id)->first();
                    $newRegistered = new Booking();
                    $newRegistered->booking = $booking;
                    $newRegistered->event = $reg_event;
                    if(isset($newRegistered)){
                        array_push($registered, $newRegistered);
                    }
                }
            }
            
            return response()->json([
                'user' => $user,
                'events' => $events,
                'images' => $images,
                'attendees' => $attendees,
                'registered' => $registered
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
