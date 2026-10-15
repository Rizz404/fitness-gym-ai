<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The gym's program. Prices live here and nowhere else: the AI Fitness Concierge may
     * only quote a price it has just read through a tool call.
     */
    public function up(): void
    {
        Schema::create('fitness_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->string('category'); // coffee | non_coffee | tea | food | snack | dessert
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('translations')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('image_url')->nullable();
            $table->json('tags')->nullable(); // vegan, vegetarian, gluten_free, signature, spicy, ...
            $table->json('requirements')->nullable(); // milk, gluten, egg, nuts, soy, ...
            $table->string('difficulty')->nullable(); // hot | iced | hot_iced | shareable ...
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_sold_out')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['gym_id', 'slug']);
            $table->index(['gym_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitness_programs');
    }
};
