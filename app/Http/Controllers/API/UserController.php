<?php

namespace App\Http\Controllers\API;

use App\Attendee;
use App\Http\Controllers\Controller;
use App\Kid;
use App\Message;
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
        $kids = array();
        $villages = array();
        $bookings = array();
        $parents = array();
        $kids = array();
        $waitlist = array();
        $hobbies = array();
        $allergies = array();
        $illnesses = array();
        try {        
            if($user->access_level == '1') { // Village user
                $events = DB::table('events')
                    ->join('users', 'events.user_id', '=', 'users.id')
                    ->where('users.id', $user->id)
                    ->select('users.name', 'users.image', 'events.*')
                    ->get();
                $images = User::find($user->id)->getImages;
                $attendees = (new Attendee)->villageAttendees($user->id, true);
                $bookings = DB::table('bookings')
                    ->join('users', 'bookings.user_id', '=', 'users.id')
                    ->where('bookings.village_id', $user->id)
                    ->where('bookings.paid', true)
                    ->select('users.name', 'users.image', 'users.id')
                    ->get();
                $waitlist = (new Attendee)->villageAttendees($user->id, false);
            }elseif($user->access_level == '2') { //Parent user
                $events = DB::table('events')
                    ->join('users', 'events.user_id', '=', 'users.id')
                    ->where('events.date', '>=', Carbon::now()->toDateString())
                    ->select('users.name', 'users.image', 'events.*')
                    ->get();
                $kids = User::find($user->id)->getKids;
                $hobbies = User::find($user->id)->getHobbies;
                $illnesses = User::find($user->id)->getIllnesses;
                $allergies = User::find($user->id)->getAllergies;
                foreach ($events as $event) {
                    $images_ = DB::table('images')->where(['event_id' => $event->id])->first();
                    if(isset($images_)){
                        array_push($images, $images_);
                    }
                }                
            }elseif ($user->access_level == '0') { //Admin user
                $villages = User::where('access_level', '1')->orderBy('id', 'DESC')->get();
                $bookings = DB::table('bookings')
                    ->join('users', 'bookings.village_id', '=', 'users.id')
                    ->where('bookings.paid', true)
                    ->select('users.name', 'users.image', 'bookings.*')
                    ->orderBy('id', 'DESC')
                    ->get();
                $parents = User::where('access_level', '2')->orderBy('id', 'DESC')->get();
                $kids = Kid::all();
                $attendees = DB::table('attendees')
                    ->join('kids', 'attendees.kid_id', '=', 'kids.id')
                    ->where('attendees.accepted', true)
                    ->select('kids.user_id', 'kids.photo')
                    ->get();
            }
            $messages =  $this->getMessages($user);
            $notifications = User::find($user->id)->getNotifications;
            return response()->json([
                'user' => $user,
                'events' => $events,
                'images' => $images,
                'attendees' => $attendees,
                'kids' => $kids,
                'villages' => $villages,
                'bookings' => $bookings,
                'parents' => $parents,
                'waitlist' => $waitlist,
                'messages' => $messages,
                'notifications' => $notifications,
                'hobbies' => $hobbies,
                'illnesses' => $illnesses,
                'allergies' => $allergies
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'Token error.'
            ], 500);
        }
    }
    public function fetchMessages() {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        return response()->json($this->getMessages($user), 200);
    }
    public function getMessages($user) {
        $messages = array();
        if($user->access_level == 0) { // admin user
            $my_messages = User::find($user->id)->getMessages()->orderBy('id', 'DESC')->get();
            foreach ($my_messages as $message) {
                $sender = DB::table('users')->where('id', $message->to)->first();
                $new_message = new Message();
                $new_message->message = $message;
                $new_message->sender = $sender;
                $new_message->unread = $this->count_unread($message->id, $user->id);
                array_push($messages, $new_message);
            }
        }elseif($user->access_level == 1) { // Village
            $my_messages = User::find($user->id)->getMessages()->orderBy('id', 'DESC')->get();
            $my_messages = DB::table('messages')->where('to', $user->id)->orWhere('user_id', $user->id)->orderBy('id', 'DESC')->get();
            foreach ($my_messages as $message) {
                $to = $message->to;
                if($message->to == $user->id) {
                    $to = $message->user_id;
                }
                $sender = DB::table('users')->where('id', $to)->first();
                $new_message = new Message();
                $new_message->message = $message;
                $new_message->sender = $sender;
                $new_message->unread = $this->count_unread($message->id, $user->id);
                array_push($messages, $new_message);
            }
        }elseif($user->access_level == 2) { //Parent user
            $my_messages = DB::table('messages')->where('to', $user->id)->orderBy('id', 'DESC')->get();
            foreach ($my_messages as $message) {
                $sender = DB::table('users')->where('id', $message->user_id)->first();
                $new_message = new Message();
                $new_message->message = $message;
                $new_message->sender = $sender;
                $new_message->unread = $this->count_unread($message->id, $user->id);
                array_push($messages, $new_message);
            }
        }
        return $messages;
    }
    public function count_unread($id, $user_id) {
        return Message::find($id)->getChats()
            ->where('read', false)
            ->where('user_id', '!=', $user_id)
            ->count();
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
    public function FetchThisUser(Request $request)
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $id = $request->id;
            $kids = [];
            $thisUser = User::where('id', $id)->first();
            if($user->access_level != '2') {
                $kids = Kid::where('user_id', $id)->get();
            }
            return response()->json([
                'thisUser' => $thisUser,
                'kids' => $kids
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
    public function destroy($id)
    {
        //
    }
}
