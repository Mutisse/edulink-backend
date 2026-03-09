<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Proposta;
use App\Models\Biblioteca;
use App\Models\Conteudo;
use App\Models\Carrinho;
use App\Models\Transacao;
use App\Models\Feedback;

class EstudanteController extends Controller
{
    // ==================== PEDIDOS ====================

    /**
     * Listar pedidos do estudante
     */
    public function getPedidos(Request $request)
    {
        $pedidos = Pedido::where('estudante_id', $request->user()->id)
            ->with(['propostas.explicador', 'explicador'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'ativos' => $pedidos->whereNotIn('status', ['Concluído', 'Cancelado'])->count(),
            'concluidos' => $pedidos->where('status', 'Concluído')->count(),
            'total' => $pedidos->count()
        ];

        return response()->json([
            'pedidos' => $pedidos,
            'stats' => $stats
        ]);
    }

    /**
     * Criar novo pedido
     */
    public function criarPedido(Request $request)
    {
        $validated = $request->validate([
            'materia' => 'required|string',
            'titulo' => 'required|string|max:100',
            'descricao' => 'required|string',
            'tipo_servico' => 'required|string',
            'nivel' => 'required|string',
            'duracao' => 'required|string',
            'prazo' => 'required|string',
            'preco' => 'required|numeric|min:50',
            'local' => 'required|string',
            'urgente' => 'boolean'
        ]);

        $pedido = Pedido::create([
            'estudante_id' => $request->user()->id,
            'status' => 'Aguardando',
            'visualizacoes' => 0,
            ...$validated
        ]);

        // Notificar explicadores (via job)
        // dispatch(new NovoPedidoNotification($pedido));

        return response()->json([
            'message' => 'Pedido criado com sucesso',
            'pedido' => $pedido
        ], 201);
    }

    /**
     * Ver detalhes do pedido
     */
    public function verPedido(Request $request, $id)
    {
        $pedido = Pedido::where('estudante_id', $request->user()->id)
            ->with(['propostas.explicador', 'explicador'])
            ->findOrFail($id);

        return response()->json($pedido);
    }

    /**
     * Aceitar proposta
     */
    public function aceitarProposta(Request $request, $id)
    {
        $proposta = Proposta::with('pedido')->findOrFail($id);

        if ($proposta->pedido->estudante_id !== $request->user()->id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        if ($proposta->pedido->status !== 'Em negociação') {
            return response()->json(['message' => 'Este pedido não está mais disponível'], 400);
        }

        // Rejeitar outras propostas
        Proposta::where('pedido_id', $proposta->pedido_id)
            ->where('id', '!=', $proposta->id)
            ->update(['status' => 'recusada']);

        // Aceitar proposta
        $proposta->update(['status' => 'aceita']);

        // Atualizar pedido
        $proposta->pedido->update([
            'status' => 'Aceito',
            'explicador_id' => $proposta->explicador_id,
            'preco_combinado' => $proposta->valor
        ]);

        return response()->json([
            'message' => 'Proposta aceita com sucesso',
            'pedido' => $proposta->pedido
        ]);
    }

    /**
     * Cancelar pedido
     */
    public function cancelarPedido(Request $request, $id)
    {
        $pedido = Pedido::where('estudante_id', $request->user()->id)->findOrFail($id);

        if (!in_array($pedido->status, ['Aguardando', 'Em negociação'])) {
            return response()->json([
                'message' => 'Não é possível cancelar este pedido'
            ], 400);
        }

        $pedido->update(['status' => 'Cancelado']);

        return response()->json([
            'message' => 'Pedido cancelado com sucesso'
        ]);
    }

    /**
     * Avaliar sessão
     */
    public function avaliarSessao(Request $request, $id)
    {
        $request->validate([
            'nota' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string'
        ]);

        $pedido = Pedido::where('estudante_id', $request->user()->id)
            ->where('id', $id)
            ->where('status', 'Concluído')
            ->firstOrFail();

        // Criar avaliação
        $avaliacao = $pedido->avaliacoes()->create([
            'estudante_id' => $request->user()->id,
            'explicador_id' => $pedido->explicador_id,
            'nota' => $request->nota,
            'comentario' => $request->comentario
        ]);

        // Atualizar média do explicador
        $explicador = $pedido->explicador->explicador;
        $total = $explicador->total_avaliacoes + 1;
        $media = (($explicador->avaliacao * $explicador->total_avaliacoes) + $request->nota) / $total;

        $explicador->update([
            'avaliacao' => round($media, 2),
            'total_avaliacoes' => $total
        ]);

        return response()->json([
            'message' => 'Avaliação enviada com sucesso',
            'avaliacao' => $avaliacao
        ]);
    }

    // ==================== BIBLIOTECA ====================

    /**
     * Listar biblioteca
     */
    public function getBiblioteca(Request $request)
    {
        $itens = Biblioteca::where('estudante_id', $request->user()->id)
            ->with('conteudo.explicador')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $itens->count(),
            'conteudos' => $itens->where('tipo', 'conteudo')->count(),
            'favoritos' => $itens->where('favorito', true)->count()
        ];

        return response()->json([
            'itens' => $itens,
            'stats' => $stats
        ]);
    }

    /**
     * Toggle favorito
     */
    public function toggleFavorito(Request $request, $id)
    {
        $item = Biblioteca::where('estudante_id', $request->user()->id)->findOrFail($id);
        $item->update(['favorito' => !$item->favorito]);

        return response()->json([
            'message' => $item->favorito ? 'Adicionado aos favoritos' : 'Removido dos favoritos',
            'favorito' => $item->favorito
        ]);
    }

    /**
     * Atualizar progresso
     */
    public function atualizarProgresso(Request $request, $id)
    {
        $request->validate([
            'progresso' => 'required|integer|min:0|max:100'
        ]);

        $item = Biblioteca::where('estudante_id', $request->user()->id)->findOrFail($id);
        $item->update([
            'progresso' => $request->progresso,
            'ultimo_acesso' => now()
        ]);

        return response()->json([
            'message' => 'Progresso atualizado',
            'progresso' => $item->progresso
        ]);
    }

    // ==================== CONTEÚDOS ====================

    /**
     * Listar conteúdos disponíveis
     */
    public function getConteudos(Request $request)
    {
        $query = Conteudo::where('status', 'Ativo')
            ->with('explicador')
            ->withCount('vendas');

        // Filtros
        if ($request->materia && $request->materia !== 'todos') {
            $query->where('materia', $request->materia);
        }

        if ($request->busca) {
            $query->where(function($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->busca . '%')
                  ->orWhere('descricao', 'like', '%' . $request->busca . '%');
            });
        }

        // Ordenação
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $conteudos = $query->paginate(20);

        return response()->json([
            'conteudos' => $conteudos,
            'materias' => Conteudo::distinct('materia')->pluck('materia')
        ]);
    }

    /**
     * Ver detalhes do conteúdo
     */
    public function verConteudo(Request $request, $id)
    {
        $conteudo = Conteudo::with('explicador')
            ->withCount('vendas')
            ->findOrFail($id);

        // Verificar se já comprou
        $comprou = Biblioteca::where('estudante_id', $request->user()->id)
            ->where('conteudo_id', $id)
            ->exists();

        // Incrementar visualizações
        $conteudo->increment('visualizacoes');

        return response()->json([
            'conteudo' => $conteudo,
            'comprou' => $comprou
        ]);
    }

    /**
     * Comprar conteúdo
     */
    public function comprarConteudo(Request $request, $id)
    {
        $conteudo = Conteudo::where('status', 'Ativo')->findOrFail($id);

        // Verificar se já comprou
        $jaComprou = Biblioteca::where('estudante_id', $request->user()->id)
            ->where('conteudo_id', $id)
            ->exists();

        if ($jaComprou) {
            return response()->json([
                'message' => 'Você já possui este conteúdo'
            ], 400);
        }

        // Verificar saldo (simulado)
        $estudante = $request->user()->estudante;
        if ($estudante->saldo < $conteudo->preco) {
            return response()->json([
                'message' => 'Saldo insuficiente'
            ], 400);
        }

        // Registrar compra
        $biblioteca = Biblioteca::create([
            'estudante_id' => $request->user()->id,
            'conteudo_id' => $conteudo->id,
            'tipo' => 'conteudo',
            'progresso' => 0
        ]);

        // Registrar transação
        Transacao::create([
            'explicador_id' => $conteudo->explicador_id,
            'conteudo_id' => $conteudo->id,
            'descricao' => 'Venda: ' . $conteudo->titulo,
            'valor' => $conteudo->preco,
            'comissao' => $conteudo->preco * 0.1,
            'tipo' => 'entrada',
            'status' => 'Concluída'
        ]);

        // Atualizar saldo do estudante
        $estudante->decrement('saldo', $conteudo->preco);

        return response()->json([
            'message' => 'Conteúdo adquirido com sucesso',
            'biblioteca' => $biblioteca
        ]);
    }

    // ==================== CARRINHO ====================

    /**
     * Listar carrinho
     */
    public function getCarrinho(Request $request)
    {
        $itens = Carrinho::where('estudante_id', $request->user()->id)
            ->with('conteudo')
            ->get();

        $total = $itens->sum(function($item) {
            return $item->conteudo->preco * $item->quantidade;
        });

        return response()->json([
            'itens' => $itens,
            'total' => $total
        ]);
    }

    /**
     * Adicionar ao carrinho
     */
    public function adicionarAoCarrinho(Request $request)
    {
        $request->validate([
            'conteudo_id' => 'required|exists:conteudos,id'
        ]);

        $item = Carrinho::firstOrCreate(
            [
                'estudante_id' => $request->user()->id,
                'conteudo_id' => $request->conteudo_id
            ],
            ['quantidade' => 1]
        );

        if (!$item->wasRecentlyCreated) {
            $item->increment('quantidade');
        }

        return response()->json([
            'message' => 'Item adicionado ao carrinho',
            'item' => $item->load('conteudo')
        ]);
    }

    /**
     * Remover do carrinho
     */
    public function removerDoCarrinho(Request $request, $id)
    {
        Carrinho::where('estudante_id', $request->user()->id)
            ->where('conteudo_id', $id)
            ->delete();

        return response()->json([
            'message' => 'Item removido do carrinho'
        ]);
    }

    /**
     * Finalizar compra
     */
    public function finalizarCompra(Request $request)
    {
        $itens = Carrinho::where('estudante_id', $request->user()->id)
            ->with('conteudo')
            ->get();

        if ($itens->isEmpty()) {
            return response()->json([
                'message' => 'Carrinho vazio'
            ], 400);
        }

        $total = $itens->sum(function($item) {
            return $item->conteudo->preco;
        });

        $estudante = $request->user()->estudante;

        if ($estudante->saldo < $total) {
            return response()->json([
                'message' => 'Saldo insuficiente'
            ], 400);
        }

        foreach ($itens as $item) {
            // Adicionar à biblioteca
            Biblioteca::create([
                'estudante_id' => $request->user()->id,
                'conteudo_id' => $item->conteudo_id,
                'tipo' => 'conteudo'
            ]);

            // Registrar transação
            Transacao::create([
                'explicador_id' => $item->conteudo->explicador_id,
                'conteudo_id' => $item->conteudo->id,
                'descricao' => 'Venda: ' . $item->conteudo->titulo,
                'valor' => $item->conteudo->preco,
                'comissao' => $item->conteudo->preco * 0.1,
                'tipo' => 'entrada',
                'status' => 'Concluída'
            ]);
        }

        // Atualizar saldo
        $estudante->decrement('saldo', $total);

        // Limpar carrinho
        Carrinho::where('estudante_id', $request->user()->id)->delete();

        return response()->json([
            'message' => 'Compra realizada com sucesso',
            'total' => $total
        ]);
    }

    // ==================== FEEDBACKS ====================

    /**
     * Enviar feedback
     */
    public function enviarFeedback(Request $request)
    {
        $request->validate([
            'tipo' => 'required|in:Sugestão,Reclamação,Dúvida,Elogio',
            'texto' => 'required|string'
        ]);

        $feedback = Feedback::create([
            'user_id' => $request->user()->id,
            'tipo' => $request->tipo,
            'texto' => $request->texto,
            'status' => 'Pendente'
        ]);

        return response()->json([
            'message' => 'Feedback enviado com sucesso',
            'feedback' => $feedback
        ], 201);
    }

    /**
     * Listar meus feedbacks
     */
    public function getMeusFeedbacks(Request $request)
    {
        $feedbacks = Feedback::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($feedbacks);
    }
}
