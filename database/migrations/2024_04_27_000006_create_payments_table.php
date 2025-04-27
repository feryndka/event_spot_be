<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::create('payments', function (Blueprint $table) {
      $table->id();
      $table->foreignId('attendee_id')->constrained('event_attendees')->onDelete('cascade');
      $table->decimal('amount', 10, 2);
      $table->string('payment_method');
      $table->string('transaction_id')->nullable();
      $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
      $table->timestamp('payment_date')->nullable();
      $table->string('midtrans_snap_token')->nullable();
      $table->string('midtrans_order_id')->nullable();
      $table->json('payment_details')->nullable();
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('payments');
  }
};
