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
    Schema::create('accident_logs', function (Blueprint $table) {
        $table->id();
        $table->string('unit_id')->default('B-01');
        $table->string('name')->nullable();
        $table->string('vehicle')->nullable();
        $table->boolean('is_collision');
        $table->float('confidence', 8, 6);
        $table->integer('sample_rate_hz')->default(100);
        $table->integer('window_sec')->default(4);
        $table->double('lat', 10, 6)->nullable();
        $table->double('lng', 10, 6)->nullable();
        $table->json('samples')->nullable(); // Menampung array 400 sampel [ax, ay, az, gx, gy, gz, speed, tilt]
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accident_logs');
    }
};
