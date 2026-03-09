<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Avaliacao;
use App\Models\Pedido;

class AvaliacaoController extends Controller
{
    /**
     * Listar avaliações recebidas (para explicador)
     */
    public function recebidas(Request $request)
    {
        $avaliacoes = Avaliacao::where('explicador_id', $request->user()->id)
            ->with(['estudante', 'pedido'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'media' => Avaliacao::where('explicador_id', $request->user()->id)->avg('nota') ?? 0,
            'total' => Avaliacao::where('explicador_id', $request->user()->id)->count(),
            'distribuicao' => [
                5 => Avaliacao::where('explicador_id', $request->user()->id)->where('nota', 5)->count(),
                4 => Avaliacao::where('explicador_id', $request->user()->id)->where('nota', 4)->count(),
                3 => Avaliacao::where('explicador_id', $request->user()->id)->where('nota', 3)->count(),
                2 => Avaliacao::where('explicador_id', $request->user()->id)->where('nota', 2)->count(),
                1 => Avaliacao::where('explicador_id', $request->user()->id)->where('nota', 1)->count(),
            ]
        ];

        return response()->json([
            'avaliacoes' => $avaliacoes,
            'stats' => $stats
        ]);
    }

    /**
     * Listar avaliações enviadas (para estudante)
     */
    public function enviadas(Request $request)
    {
        $avaliacoes = Avaliacao::where('estudante_id', $request->user()->id)
            ->with(['explicador', 'pedido'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($avaliacoes);
    }

    /**
     * Ver uma avaliação específica
     */
    public function show(Request $request, $id)
    {
        $avaliacao = Avaliacao::with(['estudante', 'explicador', 'pedido'])
            ->findOrFail($id);

        // Verificar permissão
        if ($avaliacao->estudante_id !== $request->user()->id &&
            $avaliacao->explicador_id !== $request->user()->id &&
            $request->user()->tipo !== 'admin') {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return response()->json($avaliacao);
    }

    /**
     * Responder a uma avaliação (para explicador)
     */
    public function responder(Request $request, $id)
    {
        $request->validate([
            'resposta' => 'required|string|max:500'
        ]);

        $avaliacao = Avaliacao::where('explicador_id', $request->user()->id)
            ->findOrFail($id);

        if ($avaliacao->resposta) {
            return response()->json([
                'message' => 'Esta avaliação já foi respondida'
            ], 400);
        }

        $avaliacao->update([
            'resposta' => $request->resposta
        ]);

        return response()->json([
            'message' => 'Resposta enviada com sucesso',
            'avaliacao' => $avaliacao
        ]);
    }

    /**
     * Estatísticas de um explicador específico
     */
    public function estatisticasExplicador($id)
    {
        $avaliacoes = Avaliacao::where('explicador_id', $id);

        $stats = [
            'media' => $avaliacoes->avg('nota') ?? 0,
            'total' => $avaliacoes->count(),
            'ultimas' => $avaliacoes->with('estudante')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'distribuicao' => [
                5 => (clone $avaliacoes)->where('nota', 5)->count(),
                4 => (clone $avaliacoes)->where('nota', 4)->count(),
                3 => (clone $avaliacoes)->where('nota', 3)->count(),
                2 => (clone $avaliacoes)->where('nota', 2)->count(),
                1 => (clone $avaliacoes)->where('nota', 1)->count(),
            ]
        ];

        return response()->json($stats);
    }

    /**
     * Verificar se pode avaliar (após pedido concluído)
     */
    public function podeAvaliar(Request $request, $pedidoId)
    {
        $pedido = Pedido::where('estudante_id', $request->user()->id)
            ->where('id', $pedidoId)
            ->where('status', 'Concluído')
            ->first();

        if (!$pedido) {
            return response()->json([
                'pode' => false,
                'motivo' => 'Pedido não encontrado ou não concluído'
            ]);
        }

        $jaAvaliou = Avaliacao::where('pedido_id', $pedidoId)->exists();

        return response()->json([
            'pode' => !$jaAvaliou,
            'motivo' => $jaAvaliou ? 'Você já avaliou este pedido' : null,
            'pedido' => $pedido
        ]);
    }
}
