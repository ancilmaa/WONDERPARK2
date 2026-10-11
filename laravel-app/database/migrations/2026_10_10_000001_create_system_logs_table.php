<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_logs')) {
            return;
        }

        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            // users.user_id is a plain signed INT in this database, so no FK constraint here.
            $table->integer('user_id')->nullable();
            $table->string('role', 20)->nullable();
            $table->string('action', 60);
            $table->string('description', 255);
            $table->string('subject_type', 40)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
