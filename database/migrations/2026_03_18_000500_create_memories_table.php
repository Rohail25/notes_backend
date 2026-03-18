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
        Schema::create('memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->index();
            $table->longText('description');
            $table->enum('type', ['memory', 'date', 'letter', 'achievement'])->index();
            $table->date('memory_date')->nullable()->index();
            $table->string('file_path')->nullable();
            $table->enum('visibility', ['private', 'shared'])->default('shared')->index();
            $table->dateTime('unlock_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memories');
    }
};
