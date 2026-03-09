<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('explicadores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('descricao')->nullable();
            $table->json('materias');
            $table->decimal('avaliacao', 3, 2)->default(0);
            $table->integer('total_avaliacoes')->default(0);
            $table->integer('sessoes_realizadas')->default(0);
            $table->integer('estudantes_atendidos')->default(0);
            $table->integer('taxa_aceitacao')->default(0);
            $table->decimal('preco_medio', 10, 2)->default(0);
            $table->json('disponibilidade')->nullable();
            $table->string('status')->default('Pendente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('explicadores');
    }
};
