<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('category_subscriptions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->foreignId('category_id')->constrained()->onDelete('cascade');
      $table->timestamp('created_at')->useCurrent();

      $table->unique(['user_id', 'category_id']);
    });
  }

  public function down()
  {
    Schema::dropIfExists('category_subscriptions');
  }
};
