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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('carriers')->restrictOnDelete();
            $table->foreignId('origin_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('destination_id')->constrained('locations')->restrictOnDelete();
            $table->string('cargo_type');
            $table->decimal('weight', 10, 2);
            $table->decimal('volume', 10, 2);
            $table->dateTime('expected_departure');
            $table->dateTime('expected_arrival');
            $table->dateTime('estimated_arrival');
            $table->dateTime('actual_departure')->nullable();
            $table->dateTime('actual_arrival')->nullable();
            $table->string('current_status')->default('pending');
            $table->boolean('is_delayed')->default(false);
            $table->integer('delay_hours')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('shipment_number');
            $table->index('current_status');
            $table->index('is_delayed');
            $table->index(['customer_id', 'current_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
