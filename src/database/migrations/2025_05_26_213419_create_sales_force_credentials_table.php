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
        Schema::create('sales_force_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraper_preference_id')->constrained()->onDelete('cascade');
            $table->string('username');
            $table->string('password');
            $table->string('auth_token')->nullable();
            $table->string('org_domain')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_force_credentials');
    }
};
