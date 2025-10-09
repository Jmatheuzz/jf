<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('visitas', function (Blueprint $table) {
            $table->id();
            $table->dateTime('data_visita')->nullable();
            $table->boolean('visitado')->default(false);
            $table->foreignId('imovel_id')->nullable()->constrained('imoveis');
            $table->foreignId('processo_id')->nullable()->constrained('processos_habitacionais');
            $table->boolean('confirmada')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitas');
    }
};
