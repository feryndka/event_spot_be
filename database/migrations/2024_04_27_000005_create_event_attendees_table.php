<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('event_attendees', function (Blueprint $table) {
      $table->id();
      $table->foreignId('event_id')->constrained()->onDelete('cascade');
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->timestamp('registration_date')->useCurrent();
      $table->enum('status', ['registered', 'attended', 'cancelled', 'pending_payment'])->default('registered');
      $table->string('ticket_code')->nullable();
      $table->timestamp('check_in_time')->nullable();
      $table->timestamps();

      $table->unique(['event_id', 'user_id']);
    });
  }

  public function down()
  {
    Schema::dropIfExists('event_attendees');
  }
};
