<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fitness_program_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('session_capacity')->default(12);
            $table->json('schedule_times');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->string('area_type')->default('indoor'); // indoor | outdoor | bar | private
            $table->unsignedTinyInteger('min_participants')->default(1);
            $table->unsignedTinyInteger('max_participants')->default(1);
            $table->json('features')->nullable();

            $table->decimal('joining_fee', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['gym_id', 'slug']);
        });

        Schema::create('training_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_area_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        /**
         * Mock table-booking data: the one table the AI is NEVER allowed to
         * answer from memory. A POS / reservation-system adapter can replace
         * it later without touching the AI or knowledge-base layers.
         */
        Schema::create('session_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_area_id')->constrained()->cascadeOnDelete();
            $table->date('reservation_date');
            $table->string('time_slot', 5); // HH:MM, the start of the slot
            $table->unsignedSmallInteger('capacity');
            $table->unsignedSmallInteger('booked_places')->default(0);
            $table->timestamps();
            $table->unique(['training_area_id', 'reservation_date', 'time_slot'], 'session_inventory_slot_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_inventory');
        Schema::dropIfExists('training_images');
        Schema::dropIfExists('training_areas');
    }
};
