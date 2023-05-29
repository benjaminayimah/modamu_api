<?php

namespace App\Http\Controllers\API;

use App\Attendee;
use App\Booking;
use App\Event;
use App\Http\Controllers\Controller;
use App\Kid;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use UnexpectedValueException;

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
    public function MakePayment(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $event_id = $request['event_id'];
        $event = Event::all()->where('id', $event_id)->first();
        $kid_array = $request['selection'];
        $village_id = $request['village'];
        $parent_id = $user->id;
        $quantity = count($kid_array);
        $amount = $event->amount;
        $total_amount = $quantity * $amount;
        $name = 'Paying for the event: '.$event->event_name;
        try {
            //make payment
            \Stripe\Stripe::setApiKey(config('stripe.sk'));
            $session = \Stripe\Checkout\Session::create([
                'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                    'name' => $name,
                    ],
                    'unit_amount' => $total_amount * 100,
                ],
                'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $request['url'].'/success/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $request['url'].'/canceled/{CHECKOUT_SESSION_ID}',
            ]);
            //place temp booking
            $booking = new Booking();
            $booking->user_id = $parent_id;
            $booking->village_id = $village_id;
            $booking->event_id = $event_id;
            $booking->event_name = $event->event_name;
            $booking->number_of_kids = $quantity;
            $booking->amount_per_child = $amount;
            $booking->total_amount = $total_amount;
            $booking->payment_session_id = $session->id;
            $booking->payment_type = 'card';
            $booking->save();
            foreach ($kid_array as $id) {
                $attendee = new Attendee();
                $attendee->user_id = $parent_id;
                $attendee->booking_id = $booking->id;
                $attendee->village_id = $village_id;
                $attendee->event_id = $event_id;
                $attendee->kid_id = $id;
                $attendee->save();
            }
            return response()->json($session->url, 200);

        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
    }
    public function CompleteBooking(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $session_id = $request->session_id;
            \Stripe\Stripe::setApiKey(config('stripe.sk'));
            $session = \Stripe\Checkout\Session::retrieve($session_id);
            if(!$session) {
                throw new NotFoundHttpException();
            }
            $booking = Booking::where('payment_session_id', $session_id)->first();
            if(!$booking) {
                throw new NotFoundHttpException();
            }
            if(!$booking->paid) {
                $booking->paid = true;
                $booking->update();
            }
            return response()->json('success', 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
    }
    public function CancelBooking(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $session_id = $request->session_id;
            \Stripe\Stripe::setApiKey(config('stripe.sk'));
            $session = \Stripe\Checkout\Session::retrieve($session_id);
            if($session) {
                $booking = Booking::where('payment_session_id', $session_id)
                ->where('paid', false)->first();
                if($booking) {
                    $booking->delete();
                }
                $attendees = Attendee::where('booking_id', $booking->id)->get();
                foreach ($attendees as $attendee) {
                    $attendee->delete();
                }
            }
            return response()->json('success', 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
            ], 500);
        }
    }
    public function WebHooks()
    {
        $endpoint_secret = config('stripe.wh');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
        try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
        } catch(\UnexpectedValueException $e) {
            return response('', 400);
        exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return response('', 400);
        }
        // Handle the event
        switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            $session_id = $session->id;
            $booking = Booking::where('payment_session_id', $session_id)->first();
            if($booking && !$booking->paid) {
                $booking->paid = true;
                $booking->update();
                //send email to user, village and admin
            }
        case 'checkout.session.async_payment_failed': //or expired
            $session = $event->data->object;
            $session_id = $session->id;
            $booking = Booking::where('payment_session_id', $session_id)
                ->where('paid', false)->first();
                if($booking) {
                    $booking->delete();
                    $attendees = Attendee::where('booking_id', $booking->id)->get();
                    foreach ($attendees as $attendee) {
                        $attendee->delete();
                    }
                    //send email to user, village and admin
                }
        default:
            echo 'Received unknown event type ' . $event->type;
        }
        return response('', 200);
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
            ->where(['bookings.user_id' => $user->id, 'bookings.paid' => true ])
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
        $chats = array();
        $message_id = 0;
        $parent_id = $request['parent'];
        $event_id = $request['event'];
        $parent = User::all()->where('id', $parent_id)->first();
        $kids = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where('attendees.user_id', $parent_id)
            ->where('attendees.event_id', $event_id)
        ->get();
        $message = DB::table('messages')
            ->where('to', $parent_id)
            ->where('user_id', $user->id)
            ->first();
        if(isset($message)) {
            $chats = Message::find($message->id)->getChats;
            $message_id = $message->id;
        }
        return response()->json([
            'parent' => $parent,
            'kids' => $kids,
            'chats' => $chats,
            'message_id' => $message_id
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
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $attendee = Attendee::findOrFail($id);
        $attendee->delete();
        return response()->json($id, 200);
    }
}
