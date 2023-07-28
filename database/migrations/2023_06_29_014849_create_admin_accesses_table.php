<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminAccessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->boolean('events')->default(false);
            $table->boolean('villages')->default(false);
            $table->boolean('parents')->default(false);
            $table->boolean('kids')->default(false);
            // $table->boolean('notifications')->default(false);
            $table->boolean('messages')->default(false);
            $table->boolean('bookings')->default(false);
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
        Schema::dropIfExists('admin_accesses');
    }
}
