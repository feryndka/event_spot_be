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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->boolean('is_ai_generated')->default(false);
            $table->string('poster_image')->nullable();
            $table->foreignId('promotor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('location_name');
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('registration_start');
            $table->dateTime('registration_end');
            $table->boolean('is_free')->default(true);
            $table->decimal('price', 10, 2)->nullable()->default(0.00);
            $table->integer('max_attendees')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_approved')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index('start_date');
            $table->index('end_date');
            $table->index('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
