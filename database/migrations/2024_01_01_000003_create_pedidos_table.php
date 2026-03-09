<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained('users');
            $table->foreignId('explicador_id')->nullable()->constrained('users');
            $table->string('materia');
            $table->string('titulo');
            $table->text('descricao');
            $table->string('tipo_servico');
            $table->string('nivel');
            $table->string('duracao');
            $table->string('prazo');
            $table->decimal('preco', 10, 2);
            $table->decimal('preco_combinado', 10, 2)->nullable();
            $table->string('local');
            $table->boolean('urgente')->default(false);
            $table->string('status')->default('Aguardando');
            $table->integer('visualizacoes')->default(0);
            $table->datetime('data_sessao')->nullable();
            $table->string('plataforma')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
