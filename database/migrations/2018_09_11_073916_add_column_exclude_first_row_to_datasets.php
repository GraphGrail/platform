<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnExcludeFirstRowToDatasets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->boolean('exclude_first_row');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->dropColumn('exclude_first_row');
        });
    }
}
