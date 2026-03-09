<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Proposta;
use App\Models\Conteudo;
use App\Models\Transacao;

class ExplicadorController extends Controller
{
    // ==================== PEDIDOS DISPONÍVEIS ====================

    /**
     * Listar pedidos disponíveis
     */
    public function getPedidosDisponiveis(Request $request)
    {
        $explicador = $request->user()->explicador;
        $materias = $explicador->materias ?? [];

        $query = Pedido::where('status', 'Aguardando')
            ->whereIn('materia', $materias)
            ->whereDoesntHave('propostas', function($q) use ($request) {
                $q->where('explicador_id', $request->user()->id);
            })
            ->with(['propostas.explicador', 'explicador'])
            ->with('estudante');

        // Filtros
        if ($request->materia && $request->materia !== 'todos') {
            $query->where('materia', $request->materia);
        }

        if ($request->urgentes) {
            $query->where('urgente', true);
        }

        if ($request->busca) {
            $query->where(function($q) use ($request) {
                $q->where('titulo', 'like', '%' . $request->busca . '%')
                  ->orWhere('descricao', 'like', '%' . $request->busca . '%');
            });
        }

        $pedidos = $query->orderBy('urgente', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'hoje' => Pedido::whereDate('created_at', today())->count(),
            'semana' => Pedido::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'urgentes' => Pedido::where('urgente', true)->count()
        ];

        return response()->json([
            'pedidos' => $pedidos,
            'stats' => $stats
        ]);
    }

    /**
     * Fazer proposta
     */
    public function fazerProposta(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'valor' => 'required|numeric|min:50',
            'mensagem' => 'nullable|string|max:500'
        ]);

        // Verificar se já fez proposta
        $jaFez = Proposta::where('pedido_id', $pedido->id)
            ->where('explicador_id', $request->user()->id)
            ->exists();

        if ($jaFez) {
            return response()->json([
                'message' => 'Você já fez uma proposta para este pedido'
            ], 400);
        }

        $proposta = Proposta::create([
            'pedido_id' => $pedido->id,
            'explicador_id' => $request->user()->id,
            'valor' => $request->valor,
            'mensagem' => $request->mensagem,
            'status' => 'pendente'
        ]);

        // Atualizar status do pedido
        $pedido->update(['status' => 'Em negociação']);

        return response()->json([
            'message' => 'Proposta enviada com sucesso',
            'proposta' => $proposta
        ], 201);
    }

    /**
     * Aceitar pedido (sem negociação)
     */
    public function aceitarPedido(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        if ($pedido->status !== 'Aguardando') {
            return response()->json([
                'message' => 'Este pedido não está mais disponível'
            ], 400);
        }

        // Criar proposta aceita
        $proposta = Proposta::create([
            'pedido_id' => $pedido->id,
            'explicador_id' => $request->user()->id,
            'valor' => $pedido->preco,
            'status' => 'aceita'
        ]);

        // Atualizar pedido
        $pedido->update([
            'status' => 'Aceito',
            'explicador_id' => $request->user()->id,
            'preco_combinado' => $pedido->preco
        ]);

        return response()->json([
            'message' => 'Pedido aceito com sucesso',
            'pedido' => $pedido->load('estudante')
        ]);
    }

    // ==================== MEUS PEDIDOS ====================

    /**
     * Listar meus pedidos
     */
   /**
 * Listar meus pedidos (apenas aceitos, em andamento e concluídos)
 */
public function getMeusPedidos(Request $request)
{
    $query = Pedido::where('explicador_id', $request->user()->id)
        ->with('estudante')
        ->whereIn('status', ['Aceito', 'Em andamento', 'Concluído']);

    $pedidos = $query->orderBy('created_at', 'desc')->get();

    $stats = [
        'ativos' => $pedidos->whereIn('status', ['Aceito', 'Em andamento'])->count(),
        'concluidos' => $pedidos->where('status', 'Concluído')->count(),
        'total' => $pedidos->count()
    ];

    return response()->json([
        'pedidos' => $pedidos,
        'stats' => $stats
    ]);
}
    /**
 * Listar propostas do explicador (pedidos em negociação)
 */
public function getMinhasPropostas(Request $request)
{
    $propostas = Proposta::where('explicador_id', $request->user()->id)
        ->with(['pedido.estudante'])
        ->where('status', 'pendente')
        ->orderBy('created_at', 'desc')
        ->get();

    // Formatar dados para o frontend
    $propostasFormatadas = $propostas->map(function($proposta) {
        return [
            'id' => $proposta->id,
            'pedido_id' => $proposta->pedido_id,
            'titulo' => $proposta->pedido->titulo,
            'descricao' => $proposta->pedido->descricao,
            'materia' => $proposta->pedido->materia,
            'duracao' => $proposta->pedido->duracao,
            'local' => $proposta->pedido->local,
            'nivel' => $proposta->pedido->nivel,
            'tipo_servico' => $proposta->pedido->tipo_servico,
            'urgente' => $proposta->pedido->urgente,
            'preco_original' => $proposta->pedido->preco,
            'proposta_valor' => $proposta->valor,
            'proposta_mensagem' => $proposta->mensagem,
            'proposta_status' => $proposta->status,
            'created_at' => $proposta->pedido->created_at,
            'estudante' => [
                'id' => $proposta->pedido->estudante->id,
                'nome' => $proposta->pedido->estudante->nome,
                'avatar' => $proposta->pedido->estudante->avatar,
                'nivel' => $proposta->pedido->estudante->estudante->nivel ?? null
            ]
        ];
    });

    return response()->json([
        'propostas' => $propostasFormatadas
    ]);
}

    /**
     * Iniciar sessão
     */
    public function iniciarSessao(Request $request, $id)
    {
        $pedido = Pedido::where('explicador_id', $request->user()->id)
            ->where('status', 'Aceito')
            ->findOrFail($id);

        $pedido->update(['status' => 'Em andamento']);

        return response()->json([
            'message' => 'Sessão iniciada',
            'pedido' => $pedido
        ]);
    }

    /**
     * Concluir pedido
     */
    public function concluirPedido(Request $request, $id)
    {
        $pedido = Pedido::where('explicador_id', $request->user()->id)
            ->where('status', 'Em andamento')
            ->findOrFail($id);

        $pedido->update(['status' => 'Concluído']);

        // Registrar ganho
        $transacao = Transacao::create([
            'explicador_id' => $request->user()->id,
            'pedido_id' => $pedido->id,
            'descricao' => 'Sessão: ' . $pedido->titulo,
            'valor' => $pedido->preco_combinado,
            'comissao' => $pedido->preco_combinado * 0.1,
            'tipo' => 'entrada',
            'status' => 'Concluída'
        ]);

        // Atualizar estatísticas do explicador
        $explicador = $request->user()->explicador;
        $explicador->increment('sessoes_realizadas');
        $explicador->increment('estudantes_atendidos');

        return response()->json([
            'message' => 'Pedido concluído',
            'transacao' => $transacao
        ]);
    }

    /**
     * Agendar sessão
     */
    public function agendarSessao(Request $request, $id)
    {
        $request->validate([
            'data' => 'required|date',
            'hora' => 'required',
            'plataforma' => 'required|string'
        ]);

        $pedido = Pedido::where('explicador_id', $request->user()->id)
            ->where('status', 'Aceito')
            ->findOrFail($id);

        $pedido->update([
            'data_sessao' => $request->data . ' ' . $request->hora,
            'plataforma' => $request->plataforma
        ]);

        return response()->json([
            'message' => 'Sessão agendada',
            'pedido' => $pedido
        ]);
    }

    // ==================== CONTEÚDOS ====================

    /**
     * Listar meus conteúdos
     */
    public function getMeusConteudos(Request $request)
    {
        $conteudos = Conteudo::where('explicador_id', $request->user()->id)
            ->withCount('vendas')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $conteudos->count(),
            'ativos' => $conteudos->where('status', 'Ativo')->count(),
            'pendentes' => $conteudos->where('status', 'Pendente')->count(),
            'vendas' => $conteudos->sum('vendas_count'),
            'receita' => $conteudos->sum(function($c) {
                return $c->preco * $c->vendas_count;
            })
        ];

        return response()->json([
            'conteudos' => $conteudos,
            'stats' => $stats
        ]);
    }

    /**
     * Publicar conteúdo
     */
    public function publicarConteudo(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:100',
            'materia' => 'required|string',
            'nivel' => 'required|array',
            'descricao' => 'required|string',
            'preco' => 'required|numeric|min:20',
            'arquivo' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);

        // Upload do arquivo
        $path = $request->file('arquivo')->store('conteudos', 'public');

        $conteudo = Conteudo::create([
            'explicador_id' => $request->user()->id,
            'titulo' => $validated['titulo'],
            'materia' => $validated['materia'],
            'nivel' => json_encode($validated['nivel']),
            'descricao' => $validated['descricao'],
            'preco' => $validated['preco'],
            'arquivo' => $path,
            'status' => 'Pendente',
            'visualizacoes' => 0
        ]);

        return response()->json([
            'message' => 'Conteúdo publicado. Aguardando aprovação.',
            'conteudo' => $conteudo
        ], 201);
    }

    /**
     * Editar conteúdo
     */
    public function editarConteudo(Request $request, $id)
    {
        $conteudo = Conteudo::where('explicador_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'sometimes|string|max:100',
            'descricao' => 'sometimes|string',
            'preco' => 'sometimes|numeric|min:20'
        ]);

        $conteudo->update($validated);

        return response()->json([
            'message' => 'Conteúdo atualizado',
            'conteudo' => $conteudo
        ]);
    }

    /**
     * Ativar/Desativar conteúdo
     */
    public function toggleConteudoStatus(Request $request, $id)
    {
        $conteudo = Conteudo::where('explicador_id', $request->user()->id)->findOrFail($id);

        $novoStatus = $conteudo->status === 'Ativo' ? 'Inativo' : 'Ativo';
        $conteudo->update(['status' => $novoStatus]);

        return response()->json([
            'message' => 'Status alterado',
            'status' => $novoStatus
        ]);
    }

    /**
     * Estatísticas do conteúdo
     */
    public function estatisticasConteudo(Request $request, $id)
    {
        $conteudo = Conteudo::where('explicador_id', $request->user()->id)
            ->withCount('vendas')
            ->findOrFail($id);

        $estatisticas = [
            'visualizacoes' => $conteudo->visualizacoes,
            'vendas' => $conteudo->vendas_count,
            'receita' => $conteudo->preco * $conteudo->vendas_count,
            'avaliacao_media' => $conteudo->avaliacoes()->avg('nota') ?? 0,
            'total_avaliacoes' => $conteudo->avaliacoes()->count()
        ];

        return response()->json($estatisticas);
    }

    // ==================== GANHOS ====================

    /**
     * Resumo de ganhos
     */
    public function getGanhos(Request $request)
    {
        $transacoes = Transacao::where('explicador_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $saldo = Transacao::where('explicador_id', $request->user()->id)
            ->where('tipo', 'entrada')
            ->sum('valor') -
            Transacao::where('explicador_id', $request->user()->id)
            ->where('tipo', 'saida')
            ->sum('valor');

        $periodos = [
            'hoje' => Transacao::where('explicador_id', $request->user()->id)
                ->whereDate('created_at', today())
                ->where('tipo', 'entrada')
                ->sum('valor'),
            'semana' => Transacao::where('explicador_id', $request->user()->id)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('tipo', 'entrada')
                ->sum('valor'),
            'mes' => Transacao::where('explicador_id', $request->user()->id)
                ->whereMonth('created_at', now()->month)
                ->where('tipo', 'entrada')
                ->sum('valor'),
            'total' => Transacao::where('explicador_id', $request->user()->id)
                ->where('tipo', 'entrada')
                ->sum('valor')
        ];

        return response()->json([
            'saldo' => $saldo,
            'periodos' => $periodos,
            'historico' => $transacoes
        ]);
    }

    /**
     * Solicitar saque
     */
    public function solicitarSaque(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric|min:100',
            'carteira' => 'required|in:M-Pesa,mKESh,E-Mola'
        ]);

        $saldo = Transacao::where('explicador_id', $request->user()->id)
            ->where('tipo', 'entrada')
            ->sum('valor') -
            Transacao::where('explicador_id', $request->user()->id)
            ->where('tipo', 'saida')
            ->sum('valor');

        if ($request->valor > $saldo) {
            return response()->json([
                'message' => 'Saldo insuficiente'
            ], 400);
        }

        $transacao = Transacao::create([
            'explicador_id' => $request->user()->id,
            'descricao' => 'Saque para ' . $request->carteira,
            'valor' => -$request->valor,
            'tipo' => 'saida',
            'carteira' => $request->carteira,
            'status' => 'Pendente'
        ]);

        return response()->json([
            'message' => 'Saque solicitado com sucesso',
            'transacao' => $transacao
        ]);
    }
}
