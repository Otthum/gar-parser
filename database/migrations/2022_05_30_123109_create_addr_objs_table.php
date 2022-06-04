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
        Schema::create('addr_objs', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('gar_id')->unsigned()->unique()->comment('id объекта по справочнику ГАР');
            $table->string('gar_guid')->comment('guid объекта по справочнику ГАР');

            $table->string('name')->comment('Наименование');
            $table->string('short')->comment('Краткое наименование');

            $table->string('level')->comment('Уровень адресного объекта из AS_OBJECT_LEVELS');

            /**
             * Не храним эти данные, т.к. они относятся к записи в ГАРе, а не к адресному объекту
             */
            /*
            $table->bigInteger('change_id')->nullable()->unsigned()->comment('id записи изменения из AS_CHANGE_HISTORY');
            $table->bigInteger('operation_id')->nullable()->unsigned()->comment('id типа операции из AS_OPERATION_TYPES');

            $table->bigInteger('prev_id')->default(0)->unsigned()->comment('id предыдущей записи об этом доме из AS_HOUSES');
            $table->bigInteger('next_id')->default(0)->unsigned()->comment('id следующей записи об этом доме из AS_HOUSES');

            $table->date('update_date')->comment('Дата обновления записи');
            $table->date('start_date')->comment('Дата начала действия записи');
            $table->date('end_date')->comment('Дата окончания действия записи'); */

            /**
             * Не нужны так как будем хранить только актуальные записи
             * об активных адресных объектах
             */
            /* $table->boolean('is_actual')->comment('Действительна ли запись');
            $table->boolean('is_active')->comment('Действует ли данный объект'); */

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
        Schema::dropIfExists('addr_objs');
    }
};
