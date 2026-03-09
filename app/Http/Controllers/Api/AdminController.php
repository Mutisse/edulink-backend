<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Explicador;
use App\Models\Conteudo;
use App\Models\Pedido;
use App\Models\Transacao;
use App\Models\Feedback;

class AdminController extends Controller
{
    // ==================== DASHBOARD ====================

    /**
     * Dashboard com estatísticas
     */
   public function dashboard()
    {
        $stats = [
            'usuarios' => [
                'total' => User::count(),
                'estudantes' => User::where('tipo', 'estudante')->count(),
                'explicadores' => User::where('tipo', 'explicador')->count(),
                'novos_hoje' => User::whereDate('created_at', today())->count(),
                'novos_semana' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
            ],
            'explicadores' => [
                'ativos' => Explicador::where('status', 'Ativo')->count(),
                'pendentes' => Explicador::where('status', 'Pendente')->count(),
                'total' => Explicador::count()
            ],
            'conteudos' => [
                'total' => Conteudo::count(),
                'ativos' => Conteudo::where('status', 'Ativo')->count(),
                'pendentes' => Conteudo::where('status', 'Pendente')->count(),
                'vendas' => Conteudo::withCount('vendas')->get()->sum('vendas_count')
            ],
            'pedidos' => [
                'hoje' => Pedido::whereDate('created_at', today())->count(),
                'semana' => Pedido::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'ativos' => Pedido::whereNotIn('status', ['Concluído', 'Cancelado'])->count()
            ],
            'financeiro' => [
                'receita_hoje' => Transacao::whereDate('created_at', today())->where('tipo', 'entrada')->sum('valor'),
                'receita_semana' => Transacao::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('tipo', 'entrada')->sum('valor'),
                'receita_mes' => Transacao::whereMonth('created_at', now()->month)->where('tipo', 'entrada')->sum('valor'),
                'comissao_mes' => Transacao::whereMonth('created_at', now()->month)->where('tipo', 'entrada')->sum('comissao')
            ]
        ];

        // Buscar últimas 5 transações com relacionamentos
        $ultimasTransacoes = Transacao::with(['explicador', 'pedido.estudante'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($transacao) {
                return [
                    'id' => $transacao->id,
                    'descricao' => $transacao->descricao,
                    'valor' => $transacao->valor,
                    'comissao' => $transacao->comissao,
                    'tipo' => $transacao->tipo,
                    'carteira' => $transacao->carteira,
                    'status' => $transacao->status,
                    'created_at' => $transacao->created_at,
                    'explicador_nome' => $transacao->explicador?->nome,
                    'explicador_id' => $transacao->explicador?->id,
                    'estudante_nome' => $transacao->pedido?->estudante?->nome ?? 'N/A',
                    'estudante_id' => $transacao->pedido?->estudante?->id
                ];
            });

        // Buscar top 5 explicadores por avaliação
        $topExplicadores = Explicador::with('user')
            ->where('status', 'Ativo')
            ->orderBy('avaliacao', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($exp) {
                return [
                    'id' => $exp->id,
                    'nome' => $exp->user?->nome,
                    'avatar' => $exp->user?->avatar,
                    'materias' => $exp->materias,
                    'sessoes' => $exp->sessoes_realizadas,
                    'avaliacao' => $exp->avaliacao,
                    'ganhos' => Transacao::where('explicador_id', $exp->user_id)
                        ->where('tipo', 'entrada')
                        ->sum('valor')
                ];
            });

        $atividades = [
            'usuarios' => User::latest()->limit(5)->get(['id', 'nome', 'tipo', 'created_at']),
            'transacoes' => $ultimasTransacoes,
            'pedidos' => Pedido::with('estudante', 'explicador')->latest()->limit(5)->get(),
            'feedbacks' => Feedback::with('user')->latest()->limit(5)->get(),
            'topExplicadores' => $topExplicadores
        ];

        return response()->json([
            'stats' => $stats,
            'atividades' => $atividades
        ]);
    }

    // ==================== USUÁRIOS ====================

    /**
     * Listar usuários
     */
    public function getUsuarios(Request $request)
    {
        $query = User::query();

        // Filtros
        if ($request->tipo && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->status && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        if ($request->busca) {
            $query->where(function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->busca . '%')
                    ->orWhere('email', 'like', '%' . $request->busca . '%');
            });
        }

        $usuarios = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($usuarios);
    }

    /**
     * Ver usuário
     */
    public function getUsuario($id)
    {
        $usuario = User::with(['estudante', 'explicador'])->findOrFail($id);

        // Estatísticas adicionais
        if ($usuario->tipo === 'explicador') {
            $usuario->explicador->loadCount('conteudos', 'pedidos');
            $usuario->explicador->ganhos_mes = Transacao::where('explicador_id', $id)
                ->whereMonth('created_at', now()->month)
                ->where('tipo', 'entrada')
                ->sum('valor');
        }

        if ($usuario->tipo === 'estudante') {
            $usuario->estudante->pedidos_count = Pedido::where('estudante_id', $id)->count();
            $usuario->estudante->biblioteca_count = $usuario->biblioteca()->count();
        }

        return response()->json($usuario);
    }

    /**
     * Atualizar usuário
     */
    public function atualizarUsuario(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $validated = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'telefone' => 'sometimes|string',
            'status' => 'sometimes|in:Ativo,Inativo,Bloqueado,Pendente'
        ]);

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuário atualizado',
            'usuario' => $usuario
        ]);
    }

    /**
     * Bloquear/Desbloquear usuário
     */
    public function toggleUsuarioStatus($id)
    {
        $usuario = User::findOrFail($id);

        $novoStatus = $usuario->status === 'Bloqueado' ? 'Ativo' : 'Bloqueado';
        $usuario->update(['status' => $novoStatus]);

        return response()->json([
            'message' => 'Status alterado',
            'status' => $novoStatus
        ]);
    }

    // ==================== EXPLICADORES ====================

    /**
     * Listar explicadores
     */
    public function getExplicadores(Request $request)
    {
        $query = Explicador::with('user');

        if ($request->status && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        $explicadores = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($explicadores);
    }

    /**
     * Aprovar explicador
     */
    public function aprovarExplicador($id)
    {
        $explicador = Explicador::findOrFail($id);
        $explicador->update(['status' => 'Ativo']);

        // Atualizar status do usuário
        $explicador->user->update(['status' => 'Ativo']);

        return response()->json([
            'message' => 'Explicador aprovado'
        ]);
    }

    /**
     * Reprovar explicador
     */
    public function reprovarExplicador($id)
    {
        $explicador = Explicador::findOrFail($id);
        $explicador->update(['status' => 'Inativo']);

        return response()->json([
            'message' => 'Explicador reprovado'
        ]);
    }

    // ==================== CONTEÚDOS ====================

    /**
     * Listar conteúdos
     */
    public function getConteudos(Request $request)
    {
        $query = Conteudo::with('explicador')->withCount('vendas');

        if ($request->status && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        if ($request->materia && $request->materia !== 'todos') {
            $query->where('materia', $request->materia);
        }

        $conteudos = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($conteudos);
    }

    /**
     * Aprovar conteúdo
     */
    public function aprovarConteudo($id)
    {
        $conteudo = Conteudo::findOrFail($id);
        $conteudo->update(['status' => 'Ativo']);

        return response()->json([
            'message' => 'Conteúdo aprovado'
        ]);
    }

    /**
     * Reprovar conteúdo
     */
    public function reprovarConteudo($id)
    {
        $conteudo = Conteudo::findOrFail($id);
        $conteudo->update(['status' => 'Inativo']);

        return response()->json([
            'message' => 'Conteúdo reprovado'
        ]);
    }

    /**
     * Remover conteúdo
     */
    public function removerConteudo($id)
    {
        $conteudo = Conteudo::findOrFail($id);
        $conteudo->delete();

        return response()->json([
            'message' => 'Conteúdo removido'
        ]);
    }

    // ==================== TRANSAÇÕES ====================

    /**
     * Listar transações
     */
    /**
     * Listar transações
     */
    public function getTransacoes(Request $request)
    {
        $query = Transacao::with('explicador'); // Apenas 'explicador', não 'explicador.user'

        if ($request->tipo && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->status && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        $transacoes = $query->orderBy('created_at', 'desc')->paginate(20);

        // Mapear para incluir os dados do explicador
        $transacoes->getCollection()->transform(function ($transacao) {
            return [
                'id' => $transacao->id,
                'descricao' => $transacao->descricao,
                'valor' => $transacao->valor,
                'comissao' => $transacao->comissao,
                'tipo' => $transacao->tipo,
                'carteira' => $transacao->carteira,
                'status' => $transacao->status,
                'created_at' => $transacao->created_at,
                'explicador_nome' => $transacao->explicador?->nome,
                'explicador_id' => $transacao->explicador?->id,
                'estudante_nome' => $transacao->pedido?->estudante?->nome ?? 'N/A',
                'estudante_id' => $transacao->pedido?->estudante?->id
            ];
        });

        return response()->json($transacoes);
    }

    /**
     * Resumo financeiro
     */
    public function getResumoFinanceiro()
    {
        $total_entradas = Transacao::where('tipo', 'entrada')->sum('valor');
        $total_saidas = Transacao::where('tipo', 'saida')->sum('valor');
        $total_comissao = Transacao::where('tipo', 'entrada')->sum('comissao');

        $por_mes = Transacao::selectRaw('
                YEAR(created_at) as ano,
                MONTH(created_at) as mes,
                SUM(CASE WHEN tipo = "entrada" THEN valor ELSE 0 END) as entradas,
                SUM(CASE WHEN tipo = "saida" THEN valor ELSE 0 END) as saidas,
                SUM(comissao) as comissao
            ')
            ->groupBy('ano', 'mes')
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'total_entradas' => $total_entradas,
            'total_saidas' => $total_saidas,
            'total_comissao' => $total_comissao,
            'saldo_liquido' => $total_entradas - $total_saidas,
            'por_mes' => $por_mes
        ]);
    }

    // ==================== FEEDBACKS ====================

    /**
     * Listar feedbacks
     */
    public function getFeedbacks(Request $request)
    {
        $query = Feedback::with('user');

        if ($request->tipo && $request->tipo !== 'todos') {
            $query->where('tipo', $request->tipo);
        }

        if ($request->status && $request->status !== 'todos') {
            $query->where('status', $request->status);
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($feedbacks);
    }

    /**
     * Responder feedback
     */
    public function responderFeedback(Request $request, $id)
    {
        $request->validate([
            'resposta' => 'required|string'
        ]);

        $feedback = Feedback::findOrFail($id);
        $feedback->update([
            'resposta' => $request->resposta,
            'status' => 'Respondido'
        ]);

        return response()->json([
            'message' => 'Resposta enviada',
            'feedback' => $feedback
        ]);
    }

    /**
     * Marcar como resolvido
     */
    public function resolverFeedback($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update(['status' => 'Resolvido']);

        return response()->json([
            'message' => 'Feedback marcado como resolvido'
        ]);
    }
}
