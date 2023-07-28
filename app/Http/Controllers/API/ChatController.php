<?php

namespace App\Http\Controllers\API;

use App\Chat;
use App\Email;
use App\Http\Controllers\Controller;
use App\Mail\MessageFromModamu;
use App\Mail\YouHaveANewMessage;
use App\Message;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;


class ChatController extends Controller
{
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
    public function FetchThisChats($id) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $image = '';
        $chats = array();
        $message_id = 0;
        $to = $user->id;
        $sender_id = $id;
        $image_id = $sender_id;
        if($user->access_level == 0) {//admin
            $to = $id;
            $sender_id = $user->id;
            $image_id = $id;
        }else if($user->access_level == 1) {//village
            $to = $id;
            $sender_id = $user->id;
            $image_id = $id;
            $checkSender = DB::table('users')->where('id', $id)->first()->access_level;
            if(isset($checkSender)) {
                if($checkSender == 0) {
                    $to = $user->id;
                    $sender_id = $id;
                }
            }
        }
        $message = DB::table('messages')
            ->where('to', $to)
            ->where('user_id', $sender_id)
            ->first();
        if(isset($message)) {
            $chats = Message::find($message->id)->getChats;
            $message_id = $message->id;
        }
        if($chats) {
            foreach ($chats as $key) {
                if($key->user_id != $user->id) {
                    $key->read = true;
                    $key->update();
                }
            }
        }
        $to_user = User::findOrFail($image_id);
        $image = $to_user->image;
        return response()->json([
            'chats' => $chats,
            'image' => $image,
            'id' => $message_id
        ], 200);
    }
    public function store(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $msg_id = $request['message_id'];
            $to_id = $request['to'];
            $chat = $request['chat'];
            $sender = $user->name;
            $diff = 0;
            $email = DB::table('users')->where('id', $to_id)->first()->email;
            $messages = array();
            if($msg_id == 0) {//new
                $message = new Message();
                $message->user_id = $user->id;
                $message->to = $to_id;
                $message->preview = $chat;
                $message->save();
                $messages = $this->getMessages($user);
                                
            }else {//update
                $message = Message::findOrFail($msg_id);
                $last_time = $message->updated_at;
                $message->preview = $chat;
                $message->updated_at = Carbon::now();
                $message->update();
                // send email if interval between last mess and now > 30mins
                $diff = $last_time->diffInMinutes(Carbon::now());
            }
            $newChat = new Chat();
            $newChat->message_id = $message->id;
            $newChat->user_id = $user->id;
            $newChat->chat = $chat;
            $newChat->save();

            if($msg_id == 0) {
                $this->sendEmail($email, $sender, $chat);
            }else {
                if($diff >= 30) {
                    $this->sendEmail($email, $sender, $chat);
                }
            }
            return response()->json([
                'chat' => $newChat,
                'message' => $message,
                'messages' => $messages
            ]);
        } catch (\Throwable $th) {
            return response()->json('An error has occurred', 500);
        }
    }
    public function sendEmail($email, $sender, $chat)
    {
        $data = new Email();
        $data->sender = $sender;
        $data->chat = $chat;
        $data->hideme = Carbon::now();
        $data->url = config('hosts.fe');
        Mail::to($email)->send(new YouHaveANewMessage($data));
    }
    public function SendBulkMessage(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $subject = $request['subject'];
            $message = $request['message'];
            $recipients = $request['recipients'];
            foreach ($recipients as $value) {
                $this->sendBulkEmail($value['email'], $subject, $message, $user->name);
            }
            return response()->json('Message sent', 200);
        } catch (\Throwable $th) {
            return response()->json('Error sending message', 500);
        }
    }
    public function sendBulkEmail($email, $subject, $body, $sender)
    {
        $data = new Email();
        $data->subject = $subject;
        $data->body = $body;
        $data->sender = $sender;
        $data->hideme = Carbon::now();
        Mail::to($email)->send(new MessageFromModamu($data));
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
