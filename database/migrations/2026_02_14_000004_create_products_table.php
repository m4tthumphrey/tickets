<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->dateTime('starts_at')->nullable();
            $table->string('timezone')->nullable();
            $table->boolean('time_confirmed')->default(false);
            $table->unsignedInteger('home_team_id')->nullable();
            $table->unsignedInteger('away_team_id')->nullable();
            $table->unsignedInteger('competition_id')->nullable();
            $table->unsignedInteger('venue_id')->nullable();
            $table->string('status')->nullable();
            $table->unsignedInteger('min_order')->default(1);
            $table->unsignedInteger('max_order')->default(99);
            $table->string('main_image')->nullable();
            $table->string('thumb_image')->nullable();
            $table->text('information')->nullable();
            $table->text('notes')->nullable();
            $table->text('timetable')->nullable();
            $table->string('currency', 10)->nullable();
            $table->json('categories')->nullable();
            $table->json('seating_plans')->nullable();
            $table->timestamps();

            $table->foreign('venue_id')->references('id')->on('venues')->nullOnDelete();
            $table->foreign('home_team_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreign('away_team_id')->references('id')->on('teams')->nullOnDelete();
            $table->index('type');
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
