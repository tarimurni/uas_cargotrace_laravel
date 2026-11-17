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
        Schema::table('shipment_exceptions', function (Blueprint $table) {
            $table->string('status', 30)->default('OPEN')->after('severity');
            $table->dateTime('detected_at')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipment_exceptions', function (Blueprint $table) {
            $table->dropColumn(['status', 'detected_at']);
        });
    }
};
