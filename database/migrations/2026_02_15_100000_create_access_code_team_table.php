<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_code_team', function (Blueprint $table) {
            $table->foreignId('access_code_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('team_id');
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
            $table->primary(['access_code_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_code_team');
    }
};
