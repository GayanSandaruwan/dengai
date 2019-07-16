<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMohDengueCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moh_dengue_cases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('year');
            $table->integer('week');
            $table->integer('cases');
            $table->string('moh');
            $table->integer('old_moh_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moh_dengue_cases');
    }
}
