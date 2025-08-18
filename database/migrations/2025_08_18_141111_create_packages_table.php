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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free, Plus, Pro
            $table->string('slug')->unique(); // free, plus, pro
            $table->decimal('price', 8, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->integer('max_uploads')->nullable(); // null = unlimited
            $table->integer('storage_days'); // depolama süresi
            $table->integer('upload_days'); // yükleme süresi
            $table->enum('quality', ['normal', 'high'])->default('normal');
            $table->boolean('advanced_customization')->default(false);
            $table->json('features')->nullable(); // diğer özellikler
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
