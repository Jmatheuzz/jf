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
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('imoveis', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('fotos_imovel', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('processos_habitacionais', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('processos_habitacional_history', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('visitas', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('pos_vendas', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('imoveis', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('fotos_imovel', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('processos_habitacionais', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('processos_habitacional_history', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('visitas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('pos_vendas', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('atendimentos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};