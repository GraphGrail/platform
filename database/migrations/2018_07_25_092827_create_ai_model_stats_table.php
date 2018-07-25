<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAiModelStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ai_model_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->bigInteger('model_id');
            $table->bigInteger('user_id');
            $table->longText('query')->nullable();
            $table->longText('result')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_model_stats');
    }
}
