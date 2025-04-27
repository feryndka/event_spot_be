<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('users', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('email')->unique();
      $table->string('password');
      $table->string('phone_number')->nullable();
      $table->string('profile_picture')->nullable();
      $table->text('bio')->nullable();
      $table->enum('user_type', ['admin', 'user', 'promotor'])->default('user');
      $table->boolean('is_verified')->default(false);
      $table->boolean('is_active')->default(true);
      $table->timestamp('email_verified_at')->nullable();
      $table->rememberToken();
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('users');
  }
};
