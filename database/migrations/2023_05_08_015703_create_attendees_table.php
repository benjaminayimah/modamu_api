<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('booking_id');
            $table->string('event_id');
            $table->string('village_id');
            $table->string('kid_id');
            $table->boolean('accepted')->default(false);
            $table->enum('status', [0, 1, 2, 3])->default(0);
            $table->string('security_code')->nullable();
            $table->boolean('ended')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendees');
    }
}
