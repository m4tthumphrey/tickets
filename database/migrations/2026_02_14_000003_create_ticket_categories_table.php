<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('product_type')->nullable();
            $table->unsignedInteger('venue_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('consumer_info')->nullable();
            $table->string('color')->nullable();
            $table->json('delivery_methods')->nullable();

            // Parsed fields from description
            $table->string('seat_location')->nullable();
            $table->boolean('is_hospitality')->default(false);
            $table->boolean('has_food_included')->default(false);
            $table->string('food_description')->nullable();
            $table->boolean('has_drinks_included')->default(false);
            $table->string('drinks_description')->nullable();
            $table->boolean('has_lounge_access')->default(false);
            $table->string('lounge_name')->nullable();
            $table->string('opening_times')->nullable();
            $table->string('dress_code')->nullable();
            $table->boolean('is_family_friendly')->default(true);
            $table->string('supporter_section')->nullable();
            $table->boolean('has_padded_seats')->default(false);
            $table->string('ticket_delivery')->nullable();
            $table->json('included_extras')->nullable();
            $table->string('accessibility_notes')->nullable();
            $table->text('human_description')->nullable();

            $table->timestamps();

            $table->foreign('venue_id')->references('id')->on('venues')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};
