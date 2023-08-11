<?php

namespace App\Http\Controllers\API;

use App\AdminAccess;
use App\Chat;
use App\Email;
use App\Http\Controllers\Controller;
use App\Mail\YourAccountIsReady;
use App\Message;
use App\User;
use App\VillageAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class SubAdminController extends Controller
{
    
    public function index()
    {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $sub_admins = User::where('access_level', '0')
                ->where('sub_admin', true)
                ->get();
            $village_access = DB::table('village_accesses')
                ->join('users', 'village_accesses.village_id', '=', 'users.id')
                ->select('users.name', 'users.address', 'village_accesses.*')
                ->get();
            $admin_access = AdminAccess::all();
            return response()->json([
                'sub_admins' => $sub_admins,
                'village_access' => $village_access,
                'admin_access' => $admin_access
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!',
                'status' => 'Token error.'
            ], 500);
        }
    }
    public function AllocateVillage(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $user_id = $request['user'];
            foreach ($request['selections'] as $value) {
                $allocate = new VillageAccess();
                $allocate->user_id = $user_id;
                $allocate->village_id = $value;
                $allocate->save();
            }
            $village_access = DB::table('village_accesses')
                ->join('users', 'village_accesses.village_id', '=', 'users.id')
                ->select('users.name', 'users.address', 'village_accesses.*')
                ->get();
            return response()->json($village_access, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'password' => 'required',
        ]);
        try {
            $email = $request['email'];
            $admin = new User();
            $admin->name = $request['name'];
            $admin->email = $email;
            $admin->password = bcrypt($request['password']);
            $admin->access_level = '0';
            $admin->sub_admin = true;
            $admin->email_verified = true;
            $admin->sub_level = $request['user'];
            $admin->save();
            if($request['user'] == '1') {
                $access = new AdminAccess();
                $access->user_id = $admin->id;
                $access->save();
            }
            if($request['sendEmail']) {
            //Send email
                $data = new Email();
                $data->name = $request['name'];
                $data->email = $email;
                $data->password = $request['password'];
                $data->account_type = 'administrator account';
                $data->url = config('hosts.fe');
                Mail::to($email)->send(new YourAccountIsReady($data));
            }
            return response()->json($admin, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!'
            ], 500);
        }
    }
    // public function AccessControl(Request $request)
    // {
    //     if (! $user = JWTAuth::parseToken()->authenticate()) {
    //         return response()->json(['status' => 'User not found!'], 404);
    //     }
    //     try {
    //         return response()->json($this->SaveNewAccess($request), 200);

    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'title' => 'Error!'
    //         ], 500);
    //     }
    // }
    public function UpdateAccessControl(Request $request) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $id = $request['user_id'];
        try {
            $update = User::find($id)->getSubPermissions()
                ->where('user_id', $id)->first();
            if($update) {
                $update->events = $request['events'];
                $update->villages = $request['villages'];
                $update->parents = $request['parents'];
                $update->kids = $request['kids'];
                // $update->notifications = $request['notifications'];
                $update->messages = $request['messages'];
                $update->bookings = $request['bookings'];
                $update->update();
            }else {
                $update = $this->SaveNewAccess($request);
            }
            return response()->json($update, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'title' => 'Error!'
            ], 500);
        }
    }
    public function SaveNewAccess($request) {
        $access = new AdminAccess();
        $access->user_id = $request['user_id'];
        $access->events = $request['events'];
        $access->villages = $request['villages'];
        $access->parents = $request['parents'];
        $access->kids = $request['kids'];
        // $access->notifications = $request['notifications'];
        $access->messages = $request['messages'];
        $access->bookings = $request['bookings'];
        $access->save();
        return $access;
    }
    public function RemoveVillageAllocation($id) {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        $allocate = VillageAccess::findOrFail($id);
        $allocate->delete();
        return response()->json($id, 200);
    }
    public function destroy($id)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['status' => 'User not found!'], 404);
        }
        try {
            $admin = User::findOrFail($id);
            if($admin->sub_level === '1') {
                $admin_access = AdminAccess::where('user_id', $id)->first();
                $admin_access->delete();
            }else if($admin->sub_level === '2') {
                $village_access = VillageAccess::where('user_id', $id)->get();
                foreach ($village_access as $value) {
                    $value->delete();
                }
            }
            if (Storage::disk('public')->exists($id)) {
                Storage::deleteDirectory('public/'.$id);
            }
            $messages = Message::where('user_id', $id)
                ->orWhere('to', $id)
                ->get();
            foreach ($messages as $value) {
                $chats = Chat::where('message_id', $value->id)->get();
                if($chats) {
                    foreach ($chats as $chat) {
                        $chat->delete();
                    }
                }
                $value->delete();
            }

            $admin->delete();
            return response()->json([
                'id' => $id,
                'message' => 'User is deleted'
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
