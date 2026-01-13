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
        Schema::table('processos_habitacionais', function (Blueprint $table) {
            $table->renameColumn('data_assinatura', 'data_assinatura_empreitada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('processos_habitacionais', function (Blueprint $table) {
            $table->renameColumn('data_assinatura_empreitada', 'data_assinatura');
        });
    }
};