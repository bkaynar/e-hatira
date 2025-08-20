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
        Schema::create('event_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('photo_path');
            $table->string('original_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->string('uploader_name')->nullable();
            $table->string('uploader_email')->nullable();
            $table->ipAddress('uploader_ip')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'deleted'])->default('pending');
            $table->timestamps();
            $table->index(['event_id', 'order']);
            $table->index(['event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_photos');
    }
};
