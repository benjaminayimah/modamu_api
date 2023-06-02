<?php

namespace App\Http\Controllers\API;

use App\Chat;
use App\Http\Controllers\Controller;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;


class ChatController extends Controller
{
    public function fetchMessages() {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        return response()->json($this->getMessages($user), 200);
    }
    public function getMessages($user) {
        $messages = array();
        if($user->access_level == 1 || $user->access_level == 0) { // Village user
            $my_messages = User::find($user->id)->getMessages;
            foreach ($my_messages as $message) {
                $sender = DB::table('users')->where('id', $message->to)->first();
                $new_message = new Message();
                $new_message->message = $message;
                $new_message->sender = $sender;
                $new_message->unread = $this->count_unread($message->id);
                array_push($messages, $new_message);
            }
        }elseif($user->access_level == 2) { //Parent user
            $my_messages = DB::table('messages')->where('to', $user->id)->get();
            foreach ($my_messages as $message) {
                $sender = DB::table('users')->where('id', $message->user_id)->first();
                $new_message = new Message();
                $new_message->message = $message;
                $new_message->sender = $sender;
                $new_message->unread = $this->count_unread($message->id);
                array_push($messages, $new_message);
            }
        }
        return $messages;
    }
    public function count_unread($id) {
        return Message::find($id)->getChats()
            ->where('read', false)->count();
    }
    public function FetchThisChats($id) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $chats = Message::find($id)->getChats;
        $to = Message::findOrFail($id)->user_id;
        if($user->access_level == 1) {//village
            $to = Message::findOrFail($id)->to;
        }
        $to_user = User::findOrFail($to);
        $id = $to_user->id;
        $image = $to_user->image;
        return response()->json([
            'chats' => $chats,
            'image' => $image,
            'id' => $id
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
                $message->preview = $chat;
                $message->update();
            }
            $newChat = new Chat();
            $newChat->message_id = $message->id;
            $newChat->user_id = $user->id;
            $newChat->chat = $chat;
            $newChat->save();
            return response()->json([
                'chat' => $newChat,
                'message' => $message,
                'messages' => $messages
            ]);
        } catch (\Throwable $th) {
            return response()->json('Error', 500);
        }
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
