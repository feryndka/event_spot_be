<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('followers', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('promotor_id')->constrained('users')->onDelete('cascade');
      $table->timestamp('created_at')->useCurrent();

      $table->unique(['user_id', 'promotor_id']);
    });
  }

  public function down()
  {
    Schema::dropIfExists('followers');
  }
};
