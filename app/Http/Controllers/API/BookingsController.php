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
        $waitlist = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.village_id' => $user->id, 'attendees.accepted' => false])
            ->select('kids.*', 'attendees.event_id', 'attendees.status')
            ->get();
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
        return response()->json([
            'attendees' => $this->getAttendees($user->id),
        ], 200);
    }
    public function getAttendees($user_id) {
        $attendees = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.village_id' => $user_id, 'attendees.accepted' => true])
            ->select('kids.*', 'attendees.event_id', 'attendees.status')
            ->get();
        return $attendees;
    }
    public function ParentFetchAttendees()
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $attendees = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.user_id' => $user->id, 'attendees.accepted' => true])
            ->select('kids.*', 'attendees.event_id', 'attendees.status', 'attendees.security_code')
            ->get();
        return response()->json([
            'attendees' => $attendees,
        ], 200);
    }
    public function ParentFetchRegisteredEvents() {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $registered = array();

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

        // $attendess = DB::table('attendees')
        //     ->join('kids', 'attendees.kid_id', '=', 'kids.id')
        //     ->join('bookings', 'attendees.event_id', '=', 'bookings.event_id')
        //     ->where(['attendees.user_id' => $user->id, 'attendees.accepted' => false])
        //     ->select('kids.*', 'attendees.event_id')
        //     ->get();
        return response()->json([
            'registered' => $registered,
        ], 200);
    }
    public function FetchThisKidAndParent(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $kid_id = $request['kid'];
        $parent_id = $request['parent'];
        $event_id = $request['event'];
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
        $kids = array();

        $attendess = DB::table('attendees')
            ->where('user_id', $parent_id)
            ->where('event_id', $event_id)
            ->get();
        foreach ($attendess as $key => $value) {
            $newKid = Kid::all()->where('id', $value->kid_id)->first();
            array_push($kids, $newKid);
        }
        return response()->json([
            'parent' => $parent,
            'kids' => $kids
        ], 200);
    }
    public function AcceptThisAttendee(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $attendee = Attendee::all()
                ->where('kid_id', $request['kid'])
                ->where('event_id', $request['event'])
                ->first();
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
            return response()->json([
                'attendee' => $request['kid'],
            ], 200);
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
        $event = $request['event'];
        try {
            $attendee = Attendee::all()
            ->where('kid_id', $id)
            ->where('event_id', $event)
            ->first();
            $code = '';
            for ($i = 0; $i < 5; $i++) {
                $code .= mt_rand(0, 9);
            }
            $attendee->status = '2';
            $attendee->security_code = $code;
            $attendee->update();
            $booking = Booking::all()
            ->where('event_id', $event)
            ->where('user_id', $attendee->user_id)
            ->first();
            if($booking->kids_status == '0') {
                $booking->kids_status = '2';
                $booking->update();
            }
            if($user->access_level == 1) {//village
                return response()->json($this->getKid($user->id, $id), 200);
            }else {
                return response()->json($this->getKidParent($user->id, $id), 200);
            }
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
        $event = $request['event'];
        $code = $request['checkout_code'];
        try {
            $attendee = Attendee::all()
            ->where('kid_id', $id)
            ->where('event_id', $event)
            ->first();
            if($code == $attendee->security_code) {
                $attendee->status = '3';
                $attendee->update();
                $booking = Booking::all()
                ->where('event_id', $event)
                ->where('user_id', $attendee->user_id)
                ->first();
                if($booking->kids_status != '3') {
                    $booking->kids_status = '3';
                    $booking->update();
                }
                if($user->access_level == 1) {//village
                    return response()->json($this->getKid($user->id, $id), 200);
                }else {
                    return response()->json($this->getKidParent($user->id, $id), 200);
                }
                
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
    public function getKid($user_id, $id) {
        $kid = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.village_id' => $user_id, 'attendees.kid_id' => $id])
            ->select('kids.*', 'attendees.event_id', 'attendees.status' )
            ->first();
        return $kid;
    }
    public function getKidParent($user_id, $id) {
        $kid = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.user_id' => $user_id, 'attendees.kid_id' => $id])
            ->select('kids.*', 'attendees.event_id', 'attendees.status', 'attendees.security_code' )
            ->first();
        return $kid;
    }

    // $attendees = DB::table('attendees')
    //         ->join('kids', 'attendees.kid_id', '=', 'kids.id')
    //         ->where(['attendees.user_id' => $user_id, 'attendees.kid_id' => $id])
    //         ->select('kids.*', 'attendees.event_id', 'attendees.status', 'attendees.security_code')
    //         ->get();


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
