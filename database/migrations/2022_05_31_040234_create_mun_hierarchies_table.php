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
        Schema::create('mun_hierarchies', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('gar_id')->unsigned()->unique()->comment('id объекта из справочника ГАР');
            $table->bigInteger('parent_gar_id')->unsigned()->nullable()->comment('id родительского объекта из справочника ГАР');

            /**
             * Вообще в доках указано что оно обязательно но,
             * например, в выгрузке за 27.05.2022 в регионе 99
             * обе записи не имеют этого атрибута
             */
            $table->string('oktmo')->nullable();

            $table->text('path')->comment('Адресный путь до этого объекта');

            $table->text('address_str')->nullable()->comment('Полная строка адреса для данного объекта');
            $table->fullText('address_str');

            /**
             * Не храним эти данные, т.к. они относятся к записи в ГАРе, а не к элементу иерархии
             */
            /* 
            $table->bigInteger('gar_id')->unsigned()->comment('id из AS_MUN_HIERARCHY');
            $table->bigInteger('prev_id')->default(0)->unsigned()->comment('id предыдущей записи об этом доме из AS_MUN_HIERARCHY');
            $table->bigInteger('next_id')->default(0)->unsigned()->comment('id следующей записи об этом доме из AS_MUN_HIERARCHY');
            $table->bigInteger('change_id')->unsigned()->comment('id записи изменения из AS_CHANGE_HISTORY'); */

            $table->boolean('is_active');

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
        Schema::dropIfExists('mun_hierarchies');
    }
};
