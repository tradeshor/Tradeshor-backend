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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->float('active_investment', 13, 2)->default(0.00)->after('wallet_amount');
            $table->float('earning', 13, 2)->default(0.00)->after('wallet_amount');
            $table->float('deposits', 13, 2)->default(0.00)->after('wallet_amount');
            $table->float('withdrawals', 13, 2)->default(0.00)->after('wallet_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('active_investment');
            $table->dropColumn('earning');
            $table->dropColumn('deposits');
            $table->dropColumn('withdrawals');
        });
    }
};
