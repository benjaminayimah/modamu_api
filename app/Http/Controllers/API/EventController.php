<?php

namespace App\Http\Controllers\API;

use App\Attendee;
use App\Booking;
use App\Event;
use App\Http\Controllers\Controller;
use App\Image;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getNearByEvents(Request $request) 
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $images = array();
        $events = array();
        $userLat = $request['lat'];
        $userLng = $request['lng'];
        $radius = 10; // Search within a 10km radius
        $events_ = DB::table('events')
        ->select('*')
        ->whereRaw('(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?', [$userLat, $userLng, $userLat, $radius])
        ->get();
        foreach ($events_ as $event) {
            $image = DB::table('images')->where(['event_id' => $event->id])->first();
            if(isset($image)){
                array_push($images, $image);
            }
            $distance = 6371 * acos(cos(deg2rad($userLat)) * cos(deg2rad($event->latitude)) * cos(deg2rad($event->longitude) - deg2rad($userLng)) + sin(deg2rad($userLat)) * sin(deg2rad($event->latitude)));
            $newEvent = new Event();
            $newEvent->event = $event;
            $newEvent->distance = $distance * 1000;
            array_push($events, $newEvent);
        }
        return response()->json([
            'events' => $events,
            'images' => $images
        ], 200);
        
    }
    public function store(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $this->validate($request, [
            'event_name' => 'required',
            'date' => 'required',
            'tempImage' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);
        $address = $user->address;
        $latitude = $user->latitude;
        $longitude = $user->longitude;
        try {
            $event_image = $request['tempImage'];
            $id = $user->id;
            $newevent = new Event();
            $newevent->name = $request['event_name'];
            $newevent->user_id = $id;
            $newevent->address = $address;
            $newevent->latitude = $latitude;
            $newevent->longitude = $longitude;
            $newevent->date = $request['date'];
            $newevent->start_time = $request['start_time'];
            $newevent->end_time = $request['end_time'];
            $newevent->amount = $request['amount'];
            $newevent->limit = $request['attendance_limit'];
            $newevent->description = $request['description'];
            $newevent->save();
            $event_id = $newevent->id;
            if($event_image != null) {
                $image = new Image();
                $image->event_id = $event_id;
                $image->user_id = $id;
                $image->image = $event_image;
                $image->save();
                if (Storage::disk('public')->exists($id.'/temp'.'/'.$event_image)) {
                    Storage::disk('public')->move($id.'/temp'.'/'.$event_image, $id.'/'.$event_image);
                    Storage::deleteDirectory('public/'.$id.'/temp');
                };
            }
            return response()->json([
                'event' => $newevent,
                'image' => $image
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
    }
    public function fetchThisEvent($id)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        if ($user->access_level == 1) {
            $event = User::find($user->id)->getEvents()
                ->where('id', $id)
                ->first();
            $images = User::find($user->id)->getImages()
                ->where('event_id', $id)->get();
            $village = $user;
        }else {
            $event = DB::table('events')->where('id', $id)->first();
            $images = DB::table('images')->where('event_id', $id)->get();
            $village = DB::table('users')->where('id', $event->user_id)->first();
        }
        $kids = DB::table('kids')->where('event_id', $id)->get();
        
        return response()->json([
            'event' => $event,
            'kids' => $kids,
            'images' => $images,
            'user' => $village
        ], 200);
    }
    public function addToGallery(Request $request, $event) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $id = $user->id;
            $event_image = $request['tempImage'];
            $image = new Image();
            $image->user_id = $id;
            $image->event_id = $event;
            $image->image = $event_image;
            $image->save();
            if (Storage::disk('public')->exists($id.'/temp'.'/'.$event_image)) {
                Storage::disk('public')->move($id.'/temp'.'/'.$event_image, $id.'/'.$event_image);
                Storage::deleteDirectory('public/'.$id.'/temp');
            };
            return response()->json([
                'msg' => 'success'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }

    }

    public function delThisImage($id)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $image = Image::findOrFail($id);
            if(isset($image)) {
                Storage::disk('public')->delete($user->id.'/'.$image->image);
                $image->delete();
            }
            return response()->json([
                'msg' => 'success'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
    }
    public function PlaceBooking(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $kid_array = $request['selection'];
        $event_id = $request['event_id'];
        $parent_id = $user->id;
        try {
            $booking = new Booking();
            $booking->user_id = $parent_id;
            $booking->event_id = $event_id;
            $booking->payment_type = 'cash';
            $booking->save();
            foreach ($kid_array as $id) {
                $attendee = new Attendee();
                $attendee->user_id = $parent_id;
                $attendee->event_id = $event_id;
                $attendee->kid_id = $id;
                $attendee->save();
            }
            return response()->json([
                'msg' => 'success'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
        
    }
    public function FetchThisRegisteredEvent($id) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $attendees = array();
        $booking = User::find($user->id)->getBookings()
        ->where('event_id', $id)->first();
        $event = DB::table('events')->where('id', $id)->first();
        $attendees_ = User::find($user->id)->getAttendees()
        ->where('event_id', $id)->get();
        foreach ($attendees_ as $kid) {
            $newKid = User::find($user->id)->getKids()
            ->where('id', $kid->kid_id)->first();
            array_push($attendees, $newKid);
        }
        
        return response()->json([
            'event' => $event,
            'booking' => $booking,
            'attendees' => $attendees
        ], 200);
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
