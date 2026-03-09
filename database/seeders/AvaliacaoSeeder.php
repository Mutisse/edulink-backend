<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Avaliacao;
use App\Models\Pedido;
use App\Models\User;

class AvaliacaoSeeder extends Seeder
{
    public function run(): void
    {
        // Pegar pedidos concluídos
        $pedidos = Pedido::where('status', 'Concluído')->get();

        foreach ($pedidos as $pedido) {
            // 80% de chance de ter avaliação
            if (rand(1, 100) <= 80) {
                Avaliacao::create([
                    'pedido_id' => $pedido->id,
                    'estudante_id' => $pedido->estudante_id,
                    'explicador_id' => $pedido->explicador_id,
                    'nota' => rand(4, 5), // maioria 4 ou 5 estrelas
                    'comentario' => $this->getComentarioAleatorio(),
                    'created_at' => $pedido->updated_at,
                    'updated_at' => $pedido->updated_at
                ]);
            }
        }

        // Algumas avaliações com respostas
        $avaliacoes = Avaliacao::inRandomOrder()->limit(5)->get();
        foreach ($avaliacoes as $avaliacao) {
            $avaliacao->update([
                'resposta' => 'Obrigado pela avaliação! Fico feliz em ajudar.'
            ]);
        }
    }

    private function getComentarioAleatorio()
    {
        $comentarios = [
            'Excelente explicador! Muito didático.',
            'Ajudou muito, recomendo!',
            'Ótima explicação, tirei todas as dúvidas.',
            'Muito paciente e atencioso.',
            'Aula incrível, aprendi muito!',
            'Super recomendo, nota 10!',
            'Esclareceu todas minhas dúvidas.',
            'Muito bom, voltarei a contratar.',
            'Explicação clara e objetiva.',
            'Melhor explicador da plataforma!'
        ];

        return $comentarios[array_rand($comentarios)];
    }
}
