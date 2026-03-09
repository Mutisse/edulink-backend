<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conteudos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('explicador_id')->constrained('users');
            $table->string('titulo');
            $table->string('materia');
            $table->json('nivel');
            $table->text('descricao');
            $table->decimal('preco', 10, 2);
            $table->string('arquivo');
            $table->string('capa')->nullable();
            $table->string('status')->default('Pendente');
            $table->integer('visualizacoes')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('conteudos');
    }
};
