<?php

namespace App\Http\Controllers\API;

use App\Attendee;
use App\Booking;
use App\Event;
use App\Http\Controllers\Controller;
use App\Kid;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;


class BookingsController extends Controller
{
    public function index()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $waitlist = (new Attendee)->villageAttendees($user->id, false);

        return response()->json([
            'waitlist' => $waitlist,
        ], 200);
    }
    public function PlaceBooking(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $kid_array = $request['selection'];
        $event_id = $request['event_id'];
        $village_id = $request['village'];
        $parent_id = $user->id;
        try {
            $checkRegistered = Booking::all()
            ->where('event_id', $event_id)
            ->where('user_id', $user->id)
            ->first();
            if(!isset($checkRegistered)) {
                $booking = new Booking();
                $booking->user_id = $parent_id;
                $booking->event_id = $event_id;
                $booking->village_id = $village_id;
                $booking->payment_type = 'cash';
                $booking->save();
            }
            foreach ($kid_array as $id) {
                $checkRegAttendee = Attendee::all()
                ->where('kid_id', $id)
                ->where('event_id', $event_id)
                ->first();
                if(!isset($checkRegAttendee)) {
                    $attendee = new Attendee();
                    $attendee->user_id = $parent_id;
                    $attendee->village_id = $village_id;
                    $attendee->event_id = $event_id;
                    $attendee->kid_id = $id;
                    $attendee->save();
                }else {
                    return response()->json([
                        'title' => 'Already registerd!',
                        'msg' => 'Your kid was already registered for this event. Click on the \'Track event\' button bellow to see the status of their registration.'
                    ], 200);
                }
            }
            return response()->json([
                'title' => 'Successful!',
                'msg' => 'Thank you for booking this event.Please monitor your response and track your kids by clicking on the  \'Track event\' button bellow.'
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
        
    }
    public function VillageFetchAttendees()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        return response()->json((new Attendee)->villageAttendees($user->id, true), 200);
    }
    public function ParentFetchAttendees()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        return response()->json((new Attendee)->ParentAttendees($user->id, true), 200);
    }
    public function ParentFetchRegisteredEvents() {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $registered = DB::table('bookings')
            ->join('events', 'bookings.event_id', '=', 'events.id')
            ->where('bookings.user_id', '=', $user->id)
            ->select('events.*', 'bookings.accepted')
            ->get();
        return response()->json($registered, 200);
    }
    public function FetchThisKidAndParent(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $attendee_id = $request['id'];
        $attendee = Attendee::findOrFail($attendee_id);
        $kid_id = $attendee->kid_id;
        $parent_id = $attendee->user_id;
        $event_id = $attendee->event_id;

        $kid = Kid::all()->where('id', $kid_id)->first();
        $parent = User::all()->where('id', $parent_id)->first();
        $event = Event::all()->where('id', $event_id)->first();
        $otherKids = array();

        $attendess = DB::table('attendees')
            ->where('user_id', $parent_id)
            ->where('event_id', $event_id)
            ->where('kid_id', '!=', $kid_id)
            ->get();
        foreach ($attendess as $key => $value) {
            $newKid = Kid::all()->where('id', $value->kid_id)->first();
            array_push($otherKids, $newKid);
        }
        return response()->json([
            'kid' => $kid,
            'parent' => $parent,
            'event' => $event,
            'otherkids' => $otherKids
        ], 200);
    }
    public function FetchThisParent(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $parent_id = $request['parent'];
        $event_id = $request['event'];
        $parent = User::all()->where('id', $parent_id)->first();

        $kids = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where('attendees.user_id', $parent_id)
            ->where('attendees.event_id', $event_id)
        ->get();
        return response()->json([
            'parent' => $parent,
            'kids' => $kids
        ], 200);
    }
    public function AcceptThisAttendee(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $id = $request['id'];
        try {
            $attendee = Attendee::findOrFail($id);
            $attendee->accepted = true;
            $attendee->update();
            $booking = Booking::all()
                ->where('user_id', $attendee->user_id)
                ->where('event_id', $attendee->event_id)
                ->first();
            if(!$booking->accepted) {
                $booking->accepted = true;
                $booking->update();
            }
            return response()->json($attendee, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!'
            ], 500);
        }
    }
    public function CheckInKid(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $id = $request['id'];
        try {
            $attendee = Attendee::findOrFail($id);
            $code = '';
            for ($i = 0; $i < 5; $i++) {
                $code .= mt_rand(0, 9);
            }
            $attendee->status = '2';
            $attendee->security_code = $code;
            $attendee->update();
            $booking = Booking::all()
                ->where('event_id', $attendee->event_id)
                ->where('user_id', $attendee->user_id)
                ->first();
            if($booking->kids_status == '0') {
                $booking->kids_status = '2';
                $booking->update();
            }
            return response()->json($this->getKid($id), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!'
            ], 500);
        }

    }
    public function CheckOutKid(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $this->validate($request, [
            'checkout_code' => 'required'
        ]);
        $id = $request['id'];
        $code = $request['checkout_code'];
        try {
            $attendee = Attendee::findOrFail($id);
            if($code == $attendee->security_code) {
                $attendee->status = '3';
                $attendee->update();
                $booking = Booking::all()
                    ->where('event_id', $attendee->event_id)
                    ->where('user_id', $attendee->user_id)
                    ->first();
                if($booking->kids_status != '3') {
                    $booking->kids_status = '3';
                    $booking->update();
                }
                return response()->json($this->getKid($id), 200);
            }else {
                return response()->json([
                    'error' => true,
                    'msg' => 'Error matching code. Please try again.'
                ], 202);
            }
            //verify code
            
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!'
            ], 500);
        }
    }
    public function getKid($id) {
        $kid = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where('attendees.id', $id)
            ->select('attendees.*', 'kids.kid_name', 'kids.photo', 'kids.dob', 'kids.gender')
            ->first();
        return $kid;
    }
    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

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
