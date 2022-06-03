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
        Schema::create('appartment_types', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('gar_id')->unsigned()->unique()->comment('id из AS_APARTMENT_TYPES');
            $table->string('name')->comment('Название параметра');
            $table->string('short')->nullable()->comment('Краткое название');
            $table->string('desc')->nullable()->comment('Описание');
            
            /**
             * Не храним эти данные, т.к. они относятся к записи в ГАРе, а не к типу
             */
            /* $table->date('update_date')->comment('Дата обновления записи');
            $table->date('start_date')->comment('Дата начала действия записи');
            $table->date('end_date')->comment('Дата окончания действия записи'); */
            
            /**
             * Храним только активные
             */
            /* $table->boolean('is_active'); */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appartment_types');
    }
};
