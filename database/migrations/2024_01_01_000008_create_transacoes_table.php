<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('explicador_id')->constrained('users');
            $table->foreignId('pedido_id')->nullable()->constrained();
            $table->foreignId('conteudo_id')->nullable()->constrained();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->decimal('comissao', 10, 2)->default(0);
            $table->string('tipo'); // entrada, saida
            $table->string('carteira')->nullable();
            $table->string('referencia')->nullable();
            $table->string('status')->default('Pendente');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transacoes');
    }
};
