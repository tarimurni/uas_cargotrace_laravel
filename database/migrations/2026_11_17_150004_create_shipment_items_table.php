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
        Schema::create('shipment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->string('item_name');
            $table->string('item_code')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('weight', 10, 2);
            $table->decimal('volume', 10, 2)->nullable();
            $table->decimal('declared_value', 12, 2)->nullable();
            $table->timestamps();

            $table->index('shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_items');
    }
};
