<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('instituicoes_ensino', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['escola', 'faculdade', 'instituto_tecnico'])->default('escola');
            $table->foreignId('nivel_ensino_id')->nullable()->constrained('niveis_ensino')->nullOnDelete();
            $table->text('endereco')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('telefone')->nullable();
            $table->string('website')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('instituicoes_ensino');
    }
};
