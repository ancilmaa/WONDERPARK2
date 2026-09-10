<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('paymongo_checkout_session_id')->nullable()->after('payment_rejection_reason');
            $table->string('paymongo_payment_id')->nullable()->after('paymongo_checkout_session_id');
            $table->timestamp('payment_confirmed_at')->nullable()->after('paymongo_payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['paymongo_checkout_session_id', 'paymongo_payment_id', 'payment_confirmed_at']);
        });
    }
};
