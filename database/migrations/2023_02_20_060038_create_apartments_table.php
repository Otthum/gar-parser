<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('gar_id')->unsigned()->unique()->comment('id объекта по справочнику ГАР');
            $table->string('gar_guid')->comment('guid объекта по справочнику ГАР');

            $table->string('number');
            $table->bigInteger('type_id')->unsigned()->comment('id типа из AS_APARTMENTS_TYPES');

            $table->boolean('is_active')->comment('Действует ли данный объект');

            $table->timestamps();

            $table->foreign('type_id')->references('gar_id')->on('appartment_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apartments');
    }
};
