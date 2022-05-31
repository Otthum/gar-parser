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
        Schema::create('params', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('gar_id')->unsigned()->unique()->comment('id записи из справочника ГАР');
            $table->bigInteger('object_id')->unsigned()->comment('id объекта из справочника ГАР к которому относится параметр');
            $table->bigInteger('change_id')->nullable()->unsigned()->comment('id записи изменения из AS_CHANGE_HISTORY');
            $table->bigInteger('change_end_id')->unsigned()->comment('id записи изменения, которая отклонила это изменение')->default(0);
            $table->bigInteger('type_id')->unsigned()->comment('id параметра из AS_PARAM_TYPES');
            $table->text('value')->comment('Значение параметра');
            $table->date('update_date')->comment('Дата обновления записи');
            $table->date('start_date')->comment('Дата начала действия записи');
            $table->date('end_date')->comment('Дата окончания действия записи');

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
        Schema::dropIfExists('params');
    }
};
