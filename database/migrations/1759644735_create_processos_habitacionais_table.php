<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('processos_habitacionais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('users');
            $table->foreignId('corretor_id')->nullable()->constrained('users');
            $table->foreignId('imovel_id')->nullable()->constrained('imoveis');
            $table->string('etapa')->default('COLETA_DOCUMENTACAO');
            $table->string('interesse')->default('Compra de imóvel');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('processos_habitacionais');
    }
};
