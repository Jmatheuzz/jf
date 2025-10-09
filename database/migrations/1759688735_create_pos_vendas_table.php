<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('pos_vendas', function (Blueprint $table) {
            $table->id();
            $table->string('canal')->nullable();
            $table->dateTime('data_contato')->nullable();
            $table->text('descricao')->nullable();
            $table->boolean('resolvido')->default(false);
            $table->foreignId('cliente_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_vendas');
    }
};
