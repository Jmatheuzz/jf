<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('imoveis', function (Blueprint $table) {
            $table->id();
            $table->string('cidade')->nullable();
            $table->string('endereco')->nullable();
            $table->string('tipo')->nullable();
            $table->decimal('valor', 12, 2)->nullable();
            $table->text('descricao')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->integer('numero_banheiros')->nullable();
            $table->integer('numero_quartos')->nullable();
            $table->string('nome_arquivo')->nullable();
            $table->boolean('disponivel')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('imoveis');
    }
};
