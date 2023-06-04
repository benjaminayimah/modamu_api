<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Notification extends Model
{
    protected $table = 'notifications';
    public function insertNotification($user_id, $url, $content) {
        DB::table('notifications')->insert([
            'user_id' => $user_id,
            'url' => $url,
            'content' => $content,
            'read' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
}
