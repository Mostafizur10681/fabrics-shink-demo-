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
        Schema::create('shrinkage_charts', function (Blueprint $table) {
            $table->id();
            $table->string('group_name'); // e.g. "Group 23"
            $table->string('group_code')->nullable(); // e.g. "GRP-023"
            $table->string('stage')->default('Washed'); // e.g. "Washed", "Finished", "Greige"
            $table->string('fabric_type')->nullable(); // e.g. "Cotton Twill", "Denim", "All"
            $table->decimal('length_min', 8, 2); // e.g. -4.00 or 3.00
            $table->decimal('length_max', 8, 2); // e.g. -3.00 or 4.00
            $table->decimal('width_min', 8, 2);  // e.g. -3.00 or 2.00
            $table->decimal('width_max', 8, 2);  // e.g. -2.00 or 3.00
            $table->decimal('tolerance', 5, 2)->default(0.00);
            $table->string('pattern_code')->nullable(); // e.g. "PT-23-W"
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['stage', 'is_active']);
            $table->index(['stage', 'length_min', 'length_max']);
            $table->index(['stage', 'width_min', 'width_max']);
            $table->index('group_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shrinkage_charts');
    }
};

