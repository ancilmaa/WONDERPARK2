<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separates "Undertime" (worked a short/incomplete shift — usually
     * because no shift was assigned yet, so the system can't tell what
     * time they were SUPPOSED to start) from "Late" (actually arrived
     * past their shift's grace period). These used to be lumped into the
     * same `deduction` column and the same payslip line, which made a
     * short/incomplete QR session look like a huge "late" penalty.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('undertime_deduction', 10, 2)->default(0)->after('deduction');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('undertime_deduction');
        });
    }
};
