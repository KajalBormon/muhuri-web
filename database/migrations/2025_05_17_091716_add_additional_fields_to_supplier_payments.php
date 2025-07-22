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
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->after('supplier_id');
            $table->boolean('is_editable')->default(true)->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign('purchase_id');
            $table->dropColumn('purchase_id');
            $table->dropColumn('is_editable');
        });
    }
};
