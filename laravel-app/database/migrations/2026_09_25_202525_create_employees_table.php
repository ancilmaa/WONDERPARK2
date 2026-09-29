<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * - Fresh database (no employees table yet): create the whole table.
     * - Existing database (employees table already there, e.g. made by hand):
     *   only add the columns that are still missing.
     */
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->string('employee_name')->unique();
                $table->enum('category', ['Manager', 'Team Leader', 'Staff']);
                $table->string('pin_code')->nullable();
                $table->string('qr_token')->nullable()->unique();
                $table->date('date_hired')->nullable();
                $table->date('date_ended')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->softDeletes();
            });

            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'employee_name')) {
                $table->string('employee_name')->unique()->after('id');
            }
            if (! Schema::hasColumn('employees', 'category')) {
                $table->enum('category', ['Manager', 'Team Leader', 'Staff'])->after('employee_name');
            }
            if (! Schema::hasColumn('employees', 'pin_code')) {
                $table->string('pin_code')->nullable()->after('category');
            }
            if (! Schema::hasColumn('employees', 'qr_token')) {
                $table->string('qr_token')->nullable()->unique()->after('pin_code');
            }
            if (! Schema::hasColumn('employees', 'date_hired')) {
                $table->date('date_hired')->nullable()->after('qr_token');
            }
            if (! Schema::hasColumn('employees', 'date_ended')) {
                $table->date('date_ended')->nullable()->after('date_hired');
            }
            if (! Schema::hasColumn('employees', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('date_ended');
            }
            if (! Schema::hasColumn('employees', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     * Only drops the columns this migration is responsible for (if present).
     */
    public function down(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        $columns = array_filter(
            ['employee_name', 'category', 'pin_code', 'qr_token', 'date_hired', 'date_ended', 'status', 'deleted_at'],
            fn ($col) => Schema::hasColumn('employees', $col)
        );

        if (! empty($columns)) {
            Schema::table('employees', function (Blueprint $table) use ($columns) {
                $table->dropColumn(array_values($columns));
            });
        }
    }
};