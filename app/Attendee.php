<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Attendee extends Model
{
    public function villageAttendees($id, $status) {
        $attendees = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.village_id' => $id, 'attendees.accepted' => $status])
            ->select('attendees.*', 'kids.kid_name', 'kids.photo', 'kids.dob', 'kids.gender')
        ->get();
        return $attendees;
    }
    public function ParentAttendees($id, $status) {
        $attendees = DB::table('attendees')
            ->join('kids', 'attendees.kid_id', '=', 'kids.id')
            ->where(['attendees.user_id' => $id, 'attendees.accepted' => $status])
            ->select('attendees.*', 'kids.kid_name', 'kids.photo', 'kids.dob', 'kids.gender')
        ->get();
        return $attendees;
    }
}
