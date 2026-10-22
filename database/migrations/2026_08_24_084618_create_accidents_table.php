    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
    public function up()
{
    Schema::create('accidents', function (Blueprint $table) {
        $table->id();
        $table->string('unit_id');
        $table->string('name');
        $table->string('vehicle');
        $table->float('g_force')->default(0);

        // Fitur baru: Pitch dan Roll (menggantikan tilt)
        $table->float('pitch')->default(0);
        $table->float('roll')->default(0);

        $table->float('speed')->default(0);
        $table->decimal('lat', 10, 7);
        $table->decimal('lng', 10, 7);
        $table->enum('status', ['pending', 'resolved'])->default('pending');
        $table->json('raw_payload')->nullable();
        $table->timestamps();
    });
}

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('accidents');
        }
    };
