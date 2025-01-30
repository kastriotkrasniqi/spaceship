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
        Schema::create('trade_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_id')->constrained('planets');
            $table->foreignId('destination_id')->constrained('planets');
            $table->foreignId('resource_id')->constrained('resources');
            $table->integer('quantity');
            $table->integer('frequency')->comment('in days');
            $table->date('next_delivery')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_agreements');
    }
};
