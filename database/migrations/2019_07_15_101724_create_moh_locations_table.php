<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMohLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moh_locations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->string('moh');
            $table->decimal('lat',9,6);
            $table->decimal('lon',9,6);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moh_locations');
    }
}
