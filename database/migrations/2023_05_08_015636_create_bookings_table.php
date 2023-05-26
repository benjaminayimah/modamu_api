<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('village_id');
            $table->string('event_id');
            $table->string('event_name');
            $table->string('number_of_kids');
            $table->string('amount_per_child');
            $table->string('total_amount');
            $table->boolean('accepted')->default(false);
            $table->enum('kids_status', [0, 1, 2, 3])->default(0);
            $table->string('payment_type')->nullable();
            $table->string('payment_session_id');
            $table->boolean('paid')->default(false);
            $table->boolean('parent_delete')->default(false);
            $table->boolean('village_delete')->default(false);
            $table->boolean('admin_delete')->default(false);
            $table->text('report')->nullable();
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
        Schema::dropIfExists('bookings');
    }
}
