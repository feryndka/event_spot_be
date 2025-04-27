<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('notifications', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('title');
      $table->text('content');
      $table->string('type');
      $table->boolean('is_read')->default(false);
      $table->json('data')->nullable();
      $table->timestamp('created_at')->useCurrent();

      $table->index('is_read');
    });
  }

  public function down()
  {
    Schema::dropIfExists('notifications');
  }
};
