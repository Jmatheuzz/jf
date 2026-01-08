<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documento_processo_habitacionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processo_habitacional_id')->constrained('processos_habitacionais', 'id', 'dph_processo_habitacional_id_foreign')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->string('path');
            $table->string('nome_original');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documento_processo_habitacionals');
    }
};
