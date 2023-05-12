<?php

namespace App\Http\Controllers\API;

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
                $events = User::find($user->id)->getEvents;
                $images = User::find($user->id)->getImages;
                $attendees = DB::table('attendees')
                    ->join('kids', 'attendees.kid_id', '=', 'kids.id')
                    ->where(['attendees.village_id' => $user->id, 'attendees.accepted' => true])
                    ->select('kids.*', 'attendees.event_id', 'attendees.status')
                    ->get();
            }elseif($user->access_level == 2) { //Parent user
                $events_ = DB::table('events')
                ->where('date', '=', Carbon::now()->toDateString())
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
                // $registered_ = User::find($user->id)->getBookings;
                // foreach ($registered_ as $booking) {
                //     $reg_event = DB::table('events')->where('id', $booking->event_id)->first();
                //     $newRegistered = new Booking();
                //     $newRegistered->booking = $booking;
                //     $newRegistered->event = $reg_event;
                //     if(isset($newRegistered)){
                //         array_push($registered, $newRegistered);
                //     }
                // }
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
