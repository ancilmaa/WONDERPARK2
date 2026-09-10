<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('xendit_payment_request_id')->nullable()->after('payment_rejection_reason');
            $table->string('xendit_reference_id')->nullable()->unique()->after('xendit_payment_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['xendit_payment_request_id', 'xendit_reference_id']);
        });
    }
};
