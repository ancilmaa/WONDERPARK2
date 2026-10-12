<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Void
            $table->string('void_reason')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->timestamp('voided_at')->nullable();

            // Refund
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->foreignId('refunded_by')->nullable()->constrained('users');
            $table->timestamp('refunded_at')->nullable();

            // Manual discount
            $table->string('discount_reason')->nullable();

            // Reprint
            $table->unsignedInteger('print_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropConstrainedForeignId('refunded_by');
            $table->dropColumn([
                'void_reason', 'voided_at',
                'refund_amount', 'refund_reason', 'refunded_at',
                'discount_reason', 'print_count',
            ]);
        });
    }
};