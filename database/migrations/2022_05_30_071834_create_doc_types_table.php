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
        Schema::create('doc_types', function (Blueprint $table) {
            $table->id();
            
            $table->bigInteger('gar_id')->unsigned()->unique()->comment('id из AS_NORMATIVE_DOC_TYPES');
            $table->string('name')->comment('Название');
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
        Schema::dropIfExists('doc_types');
    }
};
