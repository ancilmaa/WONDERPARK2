<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('payment_method');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_proof_path');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_submitted_at');
            $table->string('payment_rejection_reason')->nullable()->after('payment_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_path', 'payment_submitted_at', 'payment_verified_at', 'payment_rejection_reason']);
        });
    }
};
