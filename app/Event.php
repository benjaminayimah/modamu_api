<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public function getImages() {
        return $this->hasMany(Image::class);
    }
    public function getAttendees() {
        return $this->hasMany(Kid::class);
    }
}
