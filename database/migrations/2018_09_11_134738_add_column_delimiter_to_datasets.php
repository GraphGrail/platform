<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDelimiterToDatasets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->string('delimiter', 1);
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
            $table->dropColumn('delimiter');
        });
    }
}
