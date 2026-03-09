<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Explicador;
use App\Models\Estudante;
use App\Models\Pedido;
use App\Models\Conteudo;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Admin
        $admin = User::create([
            'nome' => 'Admin EduLink',
            'email' => 'admin@edulink.co.mz',
            'password' => Hash::make('admin123'),
            'tipo' => 'admin',
            'status' => 'Ativo',
            'avatar' => 'https://cdn.quasar.dev/img/avatar6.jpg'
        ]);

        // Explicadores
        $joao = User::create([
            'nome' => 'João Santos',
            'email' => 'joao@email.com',
            'password' => Hash::make('123456'),
            'tipo' => 'explicador',
            'status' => 'Ativo',
            'telefone' => '84 123 4567',
            'avatar' => 'https://cdn.quasar.dev/img/avatar3.jpg'
        ]);

        Explicador::create([
            'user_id' => $joao->id,
            'materias' => ['Matemática', 'Física'],
            'avaliacao' => 4.9,
            'total_avaliacoes' => 127,
            'sessoes_realizadas' => 245,
            'estudantes_atendidos' => 98,
            'taxa_aceitacao' => 85,
            'preco_medio' => 750,
            'status' => 'Ativo'
        ]);

        $ana = User::create([
            'nome' => 'Ana Oliveira',
            'email' => 'ana@email.com',
            'password' => Hash::make('123456'),
            'tipo' => 'explicador',
            'status' => 'Ativo',
            'telefone' => '84 123 4568',
            'avatar' => 'https://cdn.quasar.dev/img/avatar4.jpg'
        ]);

        Explicador::create([
            'user_id' => $ana->id,
            'materias' => ['Matemática'],
            'avaliacao' => 4.8,
            'total_avaliacoes' => 98,
            'sessoes_realizadas' => 156,
            'estudantes_atendidos' => 64,
            'taxa_aceitacao' => 90,
            'preco_medio' => 680,
            'status' => 'Ativo'
        ]);

        $carlos = User::create([
            'nome' => 'Carlos Tembe',
            'email' => 'carlos@email.com',
            'password' => Hash::make('123456'),
            'tipo' => 'explicador',
            'status' => 'Ativo',
            'telefone' => '84 123 4569',
            'avatar' => 'https://cdn.quasar.dev/img/avatar1.jpg'
        ]);

        Explicador::create([
            'user_id' => $carlos->id,
            'materias' => ['Física', 'Biologia'],
            'avaliacao' => 5.0,
            'total_avaliacoes' => 86,
            'sessoes_realizadas' => 112,
            'estudantes_atendidos' => 45,
            'taxa_aceitacao' => 95,
            'preco_medio' => 800,
            'status' => 'Ativo'
        ]);

        // Estudantes
        $maria = User::create([
            'nome' => 'Maria Silva',
            'email' => 'maria@email.com',
            'password' => Hash::make('123456'),
            'tipo' => 'estudante',
            'status' => 'Ativo',
            'telefone' => '84 123 4570',
            'avatar' => 'https://cdn.quasar.dev/img/avatar2.jpg'
        ]);

        Estudante::create([
            'user_id' => $maria->id,
            'nivel' => '12ª Classe',
            'instituicao' => 'Escola Secundária da Matola',
            'pedidos_realizados' => 5,
            'sessoes_realizadas' => 3,
            'saldo' => 1250
        ]);

        $pedro = User::create([
            'nome' => 'Pedro Langa',
            'email' => 'pedro@email.com',
            'password' => Hash::make('123456'),
            'tipo' => 'estudante',
            'status' => 'Ativo',
            'telefone' => '84 123 4571',
            'avatar' => 'https://cdn.quasar.dev/img/avatar6.jpg'
        ]);

        Estudante::create([
            'user_id' => $pedro->id,
            'nivel' => '11ª Classe',
            'instituicao' => 'Escola Secundária da Matola',
            'pedidos_realizados' => 3,
            'sessoes_realizadas' => 2,
            'saldo' => 850
        ]);

        // Pedidos
        Pedido::create([
            'estudante_id' => $maria->id,
            'materia' => 'Matemática',
            'titulo' => 'Ajuda com Equações Diferenciais',
            'descricao' => 'Preciso de ajuda para entender EDO de primeira ordem',
            'tipo_servico' => 'Sessão ao vivo',
            'nivel' => '12ª Classe',
            'duracao' => '60 min',
            'prazo' => 'Hoje',
            'preco' => 800,
            'local' => 'Online',
            'urgente' => true,
            'status' => 'Em negociação',
            'visualizacoes' => 15
        ]);

        Pedido::create([
            'estudante_id' => $pedro->id,
            'materia' => 'Física',
            'titulo' => 'Relatório de Física',
            'descricao' => 'Revisão de relatório sobre termodinâmica',
            'tipo_servico' => 'Revisão de trabalho',
            'nivel' => '11ª Classe',
            'duracao' => '90 min',
            'prazo' => 'Amanhã',
            'preco' => 600,
            'local' => 'Presencial',
            'urgente' => false,
            'status' => 'Aguardando',
            'visualizacoes' => 8
        ]);

        // Conteúdos
        Conteudo::create([
            'explicador_id' => $joao->id,
            'titulo' => 'Equações Diferenciais - Guia Completo',
            'materia' => 'Matemática',
            'nivel' => ['12ª Classe', 'Ensino Superior'],
            'descricao' => 'Guia passo a passo com exercícios resolvidos',
            'preco' => 75,
            'arquivo' => 'conteudos/equacoes.pdf',
            'status' => 'Ativo',
            'visualizacoes' => 1250
        ]);

        Conteudo::create([
            'explicador_id' => $carlos->id,
            'titulo' => 'Termodinâmica - Exercícios Resolvidos',
            'materia' => 'Física',
            'nivel' => ['11ª Classe', '12ª Classe'],
            'descricao' => '20 exercícios resolvidos sobre termodinâmica',
            'preco' => 90,
            'arquivo' => 'conteudos/termodinamica.pdf',
            'status' => 'Ativo',
            'visualizacoes' => 980
        ]);
    }
}
