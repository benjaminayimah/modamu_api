<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Kid extends Model
{
    public function getHobbies() {
        return $this->hasMany(Hobby::class);
    }
    public function getIllnesses() {
        return $this->hasMany(Illness::class);
    }
    public function getAllergies() {
        return $this->hasMany(Allergy::class);
    }
}
