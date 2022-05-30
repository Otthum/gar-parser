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
        Schema::create('houses', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('gar_id')->unsigned()->unique()->comment('id из AS_HOUSES');
            $table->bigInteger('object_id')->unsigned()->comment('id объекта по справочнику ГАР');
            $table->string('object_guid')->comment('guid объекта по справочнику ГАР');

            $table->bigInteger('change_id')->nullable()->unsigned()->comment('id записи изменения из AS_CHANGE_HISTORY');

            $table->string('num')->nullable()->comment('Номер дома');
            $table->string('num_1')->nullable()->comment('Доп. номер дома');
            $table->string('num_2')->nullable()->comment('Доп. номер дома 2');

            $table->bigInteger('type_id')->nullable()->unsigned()->comment('id типа из AS_HOUSE_TYPES');
            $table->bigInteger('add_type_id_1')->nullable()->unsigned()->comment('id типа из AS_ADDHOUSE_TYPES');
            $table->bigInteger('add_type_id_2')->nullable()->unsigned()->comment('id типа из AS_ADDHOUSE_TYPES');

            $table->bigInteger('operation_id')->nullable()->unsigned()->comment('id типа операции из AS_OPERATION_TYPES');

            /**
             * У последней записи в цепочке is_active = 1, у остальных = 0
             */
            $table->bigInteger('prev_id')->nullable()->unsigned()->comment('id предыдущей записи об этом доме из AS_HOUSES');
            $table->bigInteger('next_id')->nullable()->unsigned()->comment('id следующей записи об этом доме из AS_HOUSES');

            $table->date('update_date')->comment('Дата обновления записи');
            $table->date('start_date')->comment('Дата начала действия записи');
            $table->date('end_date')->comment('Дата окончания действия записи');

            $table->boolean('is_actual')->comment('Действительна ли запись');
            $table->boolean('is_active')->comment('Действует ли данный объект');

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
        Schema::dropIfExists('houses');
    }
};
