<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('fotos_imovel', function (Blueprint $table) {
            $table->id();
            $table->string('nome_arquivo')->nullable();
            $table->string('caminho')->nullable();
            $table->integer('ordem')->default(0);
            $table->foreignId('imovel_id')->constrained('imoveis')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fotos_imovel');
    }
};
