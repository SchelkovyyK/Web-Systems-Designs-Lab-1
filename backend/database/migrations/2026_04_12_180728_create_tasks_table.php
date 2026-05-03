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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo');
<<<<<<< HEAD
            $table->integer('album_number')->nullable();
            $table->string('priority');
=======
            $table->string('priority')->default('medium');
            $table->integer('album_number');
>>>>>>> d3d75c5 (Project 4: add Redis caching and Docker deployment)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
