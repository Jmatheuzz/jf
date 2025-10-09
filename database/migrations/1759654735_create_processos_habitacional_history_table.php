<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up()
    {
        Schema::create('processos_habitacional_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_id')->constrained('processos_habitacionais')->cascadeOnDelete();
            $table->string('etapa')->default('COLETA_DOCUMENTACAO');
            $table->text('observacao')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('processos_habitacional_history');
    }
};
