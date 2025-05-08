<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('event_ratings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('event_id')->constrained()->onDelete('cascade');
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->integer('rating')->comment('Rating from 1-5');
      $table->text('review')->nullable();
      $table->boolean('is_approved')->default(false);
      $table->timestamp('reviewed_at')->nullable();
      $table->timestamps();

      // Ensure a user can only rate an event once
      $table->unique(['event_id', 'user_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('event_ratings');
  }
};
