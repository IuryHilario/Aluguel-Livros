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
        Schema::create('usuario', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('nome');
            $table->string('email')->unique();
            $table->string('telefone')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();  
        });
        
        Schema::create('livro', function (Blueprint $table) {
            $table->id('id_livro');
            $table->string('titulo');
            $table->string('autor');
            $table->string('editor')->nullable();
            $table->integer('ano_publicacao');
            $table->binary('capa')->nullable();
            $table->integer('quantidade')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();  
        });

        Schema::create('aluguel', function (Blueprint $table) {
            $table->id('id_aluguel');
            $table->foreignId('id_usuario')->constrained('usuario', 'id_usuario')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('id_livro')->constrained('livro', 'id_livro')->onDelete('cascade')->onUpdate('cascade');
            $table->date('dt_aluguel');
            $table->date('dt_devolucao');
            $table->date('dt_devolucao_efetiva')->nullable();
            $table->string('ds_status');
            $table->integer('nu_renovacoes')->default(0);
            $table->timestamps();
        });

        Schema::create('renovacao', function (Blueprint $table) {
            $table->id('id_renovacao');
            $table->foreignId('id_aluguel')->constrained('aluguel', 'id_aluguel')->onDelete('cascade')->onUpdate('cascade');
            $table->date('dt_renovacao');
            $table->date('dt_devolucao_nova');
            $table->string('ds_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('livro');
        Schema::dropIfExists('aluguel');

    }
};
