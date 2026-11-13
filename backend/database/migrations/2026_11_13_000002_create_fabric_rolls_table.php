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
        Schema::create('fabric_rolls', function (Blueprint $table) {
            $table->id();
            $table->string('roll_number')->unique();
            $table->string('lot_number');
            $table->string('fabric_type');
            $table->string('buyer')->nullable();
            $table->string('order_number')->nullable();
            $table->string('stage')->default('Washed');
            $table->decimal('roll_length', 10, 2)->nullable(); // meters or yards
            $table->date('test_date');
            $table->string('tested_by')->nullable();

            // 6 Shrinkage readings (percentages)
            $table->decimal('length_reading_1', 6, 2);
            $table->decimal('length_reading_2', 6, 2);
            $table->decimal('length_reading_3', 6, 2);
            $table->decimal('width_reading_1', 6, 2);
            $table->decimal('width_reading_2', 6, 2);
            $table->decimal('width_reading_3', 6, 2);

            // Calculated Averages
            $table->decimal('length_shrinkage_avg', 6, 2);
            $table->decimal('width_shrinkage_avg', 6, 2);

            // Group Classification Results
            $table->string('matched_group')->nullable();
            $table->foreignId('matched_chart_id')->nullable()->constrained('shrinkage_charts')->nullOnDelete();
            $table->enum('classification_status', ['MATCHED', 'NO_MATCH', 'MULTIPLE_MATCHES', 'PENDING'])->default('PENDING');
            $table->boolean('is_boundary_match')->default(false);
            $table->json('matching_details')->nullable();
            $table->timestamp('classified_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('stage');
            $table->index('matched_group');
            $table->index('classification_status');
            $table->index('test_date');
            $table->index('lot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fabric_rolls');
    }
};

