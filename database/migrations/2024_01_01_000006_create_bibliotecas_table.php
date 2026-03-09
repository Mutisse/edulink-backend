<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bibliotecas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudante_id')->constrained('users');
            $table->foreignId('conteudo_id')->constrained();
            $table->string('tipo'); // conteudo, sessao
            $table->integer('progresso')->default(0);
            $table->boolean('favorito')->default(false);
            $table->datetime('ultimo_acesso')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bibliotecas');
    }
};
