<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('comments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('event_id')->constrained()->onDelete('cascade');
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->text('content');
      $table->boolean('is_approved')->default(true);
      $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('set null');
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('comments');
  }
};
