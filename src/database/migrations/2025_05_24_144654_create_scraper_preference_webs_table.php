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
        Schema::create('scraper_preference_webs', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->longText('content')->nullable();
            $table->foreignId('scraper_preference_id')->constrained()->onDelete('cascade');
            $table->timestamp('last_scraped_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraper_preference_webs');
    }
};
