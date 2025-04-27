<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('user_preferences', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->boolean('email_notifications')->default(true);
      $table->boolean('push_notifications')->default(true);
      $table->boolean('event_reminders')->default(true);
      $table->json('preferred_categories')->nullable();
      $table->json('preferred_locations')->nullable();
      $table->timestamps();

      $table->unique('user_id');
    });
  }

  public function down()
  {
    Schema::dropIfExists('user_preferences');
  }
};
