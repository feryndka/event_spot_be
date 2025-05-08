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
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('page_views')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->decimal('engagement_rate', 5, 2)->default(0.00);
            $table->decimal('click_through_rate', 5, 2)->default(0.00);
            $table->date('data_date');
            $table->timestamps();

            $table->unique(['event_id', 'data_date'], 'unique_event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics');
    }
};
