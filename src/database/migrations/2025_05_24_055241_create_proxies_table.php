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
        Schema::create('proxies', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->integer('port');
            $table->string('protocol')->default('http');
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('country')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('response_time')->nullable();
            $table->timestamp('last_checked')->nullable();
            $table->integer('fail_count')->default(0);
            $table->timestamps();

            $table->unique(['ip_address', 'port', 'protocol']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proxies');
    }
};
