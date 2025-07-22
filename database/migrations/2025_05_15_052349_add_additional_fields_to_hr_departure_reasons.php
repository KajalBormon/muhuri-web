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
        Schema::table('hr_departure_reasons', function (Blueprint $table) {
            $table->unsignedBigInteger('central_id')->nullable()->after('id');
            $table->boolean('is_active')->default(true)->after('slug');
            $table->boolean('is_editable')->default(true)->after('is_active');
            $table->foreignId('created_by')->constrained('users')->after('is_editable');
            $table->foreignId('updated_by')->constrained('users')->after('created_by');
            $table->softDeletes();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->after('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_departure_reasons', function (Blueprint $table) {
            $table->dropColumn('central_id');
            $table->dropColumn('is_active');
            $table->dropSoftDeletes('deleted_at');
            $table->dropForeign(['deleted_by']);
            $table->dropColumn('deleted_by');
        });
    }
};
