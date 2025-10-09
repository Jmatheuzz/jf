<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->string('cpf')->nullable()->unique();
            $table->string('estado_civil')->nullable();
            $table->string('profissao')->nullable();
            $table->decimal('renda', 12, 2)->nullable();
            $table->string('rg')->nullable();
            $table->string('creci')->nullable();
            $table->string('role')->nullable();
            $table->string('code')->nullable();
            $table->boolean('possui_fgts')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->timestamps();
        });

        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'jf.imobiliariacrateus@gmail.com',
            'password' => Hash::make('jf.imobiliariacrateus@gmail.com'),
            'role' => 'ADMIN',
            'created_at' => now(),
            'updated_at' => now(),
            'email_verified_at' => now()
        ]);
    }
    
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
