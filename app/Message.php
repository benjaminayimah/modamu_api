<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Message extends Model
{
    public function getChats() {
        return $this->hasMany(Chat::class);
    }
}
