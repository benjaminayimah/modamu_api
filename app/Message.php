<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public function getChats() {
        return $this->hasMany(Chat::class);
    }
}
