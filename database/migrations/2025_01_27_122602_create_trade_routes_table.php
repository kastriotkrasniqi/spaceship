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
        Schema::create('trade_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('origin_id')->constrained('planets');
            $table->foreignId( 'destination_id')->constrained('planets');
            $table->foreignId('resource_id')->constrained('resources');
            $table->integer('quantity');
            $table->integer('travel_time')->comment('in hours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_routes');
    }
};
