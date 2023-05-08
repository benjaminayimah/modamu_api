<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kids', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('event_id')->nullable();
            $table->string('name');
            $table->string('photo')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('height')->nullable();
            $table->enum('status', [0, 1, 2, 3])->default(0);
            $table->text('about')->nullable();
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
        Schema::dropIfExists('kids');
    }
}
