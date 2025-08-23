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
        Schema::table('event_photos', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'status']);
        });
        
        Schema::table('event_photos', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('event_photos', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'deleted', 'processing', 'processed', 'failed'])->default('pending');
            $table->index(['event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_photos', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'status']);
        });
        
        Schema::table('event_photos', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('event_photos', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'deleted'])->default('pending');
            $table->index(['event_id', 'status']);
        });
    }
};
