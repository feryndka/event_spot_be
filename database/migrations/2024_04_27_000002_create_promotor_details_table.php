<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('promotor_details', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained()->onDelete('cascade');
      $table->string('company_name')->nullable();
      $table->string('company_logo')->nullable();
      $table->text('description')->nullable();
      $table->string('website')->nullable();
      $table->json('social_media')->nullable();
      $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
      $table->string('verification_document')->nullable();
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('promotor_details');
  }
};
