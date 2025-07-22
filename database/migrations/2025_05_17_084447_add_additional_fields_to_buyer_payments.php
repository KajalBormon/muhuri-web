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
        Schema::table('buyer_payments', function (Blueprint $table) {
            $table->foreignId('sale_id')->nullable()->constrained('sales')->after('buyer_id');
            $table->boolean('is_editable')->default(true)->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buyer_payments', function (Blueprint $table) {
            $table->dropForeign('sale_id');
            $table->dropColumn('sale_id');
            $table->dropColumn('is_editable');
        });
    }
};
