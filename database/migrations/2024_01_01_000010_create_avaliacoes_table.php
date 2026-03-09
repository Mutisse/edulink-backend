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
        Schema::create('avaliacoes', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('pedido_id')->constrained()->onDelete('cascade');
            $table->foreignId('estudante_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('explicador_id')->constrained('users')->onDelete('cascade');

            // Campos da avaliação
            $table->integer('nota')->comment('1 a 5 estrelas');
            $table->text('comentario')->nullable();
            $table->text('resposta')->nullable()->comment('Resposta do explicador');

            $table->timestamps();

            // Índices para buscas rápidas
            $table->index(['explicador_id', 'nota']);
            $table->index(['estudante_id', 'created_at']);

            // Garantir que um pedido só tenha uma avaliação
            $table->unique('pedido_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacoes');
    }
};
