<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Materia;
use App\Models\TipoServico;
use App\Models\NivelEnsino;
use App\Models\PrazoEntrega;
use App\Models\InstituicaoEnsino;
use App\Models\ConfiguracaoSistema;

class ConfiguracaoController extends Controller
{
    /**
     * Listar todas as matérias
     */
    public function getMaterias()
    {
        $materias = Materia::where('ativo', true)
            ->orderBy('ordem')
            ->get(['id', 'nome', 'icone', 'cor']);

        return response()->json([
            'materias' => $materias
        ]);
    }

    /**
     * Listar todos os tipos de serviço
     */
    public function getTiposServico()
    {
        $tipos = TipoServico::where('ativo', true)
            ->orderBy('ordem')
            ->get(['id', 'nome', 'icone', 'descricao']);

        return response()->json([
            'tiposServico' => $tipos
        ]);
    }

    /**
     * Listar todos os níveis de ensino
     */
    public function getNiveisEnsino()
    {
        $niveis = NivelEnsino::where('ativo', true)
            ->orderBy('ordem')
            ->get(['id', 'nome', 'descricao']);

        return response()->json([
            'niveis' => $niveis
        ]);
    }

    /**
     * Listar todos os prazos de entrega
     */
    public function getPrazos()
    {
        $prazos = PrazoEntrega::where('ativo', true)
            ->orderBy('prioridade')
            ->get(['id', 'nome', 'icone', 'dias', 'permite_mapa']);

        return response()->json([
            'prazos' => $prazos
        ]);
    }

    /**
     * Buscar instituições próximas
     */
    public function getInstituicoesProximas(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'raio' => 'nullable|numeric|min:1|max:50'
        ]);

        $raio = $request->get('raio', 10);

        $instituicoes = InstituicaoEnsino::with('nivelEnsino')
            ->where('ativo', true)
            ->proximas($request->lat, $request->lng, $raio)
            ->limit(20)
            ->get()
            ->map(function($inst) {
                return [
                    'id' => $inst->id,
                    'nome' => $inst->nome,
                    'tipo' => $inst->tipo,
                    'nivel' => $inst->nivelEnsino?->nome,
                    'endereco' => $inst->endereco,
                    'distancia' => round($inst->distancia, 1) . 'km',
                    'latitude' => $inst->latitude,
                    'longitude' => $inst->longitude
                ];
            });

        return response()->json([
            'instituicoes' => $instituicoes
        ]);
    }

    /**
     * Buscar instituições por nome
     */
    public function buscarInstituicoes(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:3'
        ]);

        $instituicoes = InstituicaoEnsino::with('nivelEnsino')
            ->where('ativo', true)
            ->where('nome', 'like', '%' . $request->q . '%')
            ->limit(20)
            ->get()
            ->map(function($inst) {
                return [
                    'id' => $inst->id,
                    'nome' => $inst->nome,
                    'tipo' => $inst->tipo,
                    'nivel' => $inst->nivelEnsino?->nome,
                    'endereco' => $inst->endereco,
                    'latitude' => $inst->latitude,
                    'longitude' => $inst->longitude
                ];
            });

        return response()->json([
            'instituicoes' => $instituicoes
        ]);
    }

    /**
     * Obter todas as configurações do sistema
     */
    public function getSistema()
    {
        $urls = ConfiguracaoSistema::get('urls', [
            'avatar_aluno_padrao' => 'https://cdn.quasar.dev/img/avatar2.jpg',
            'avatar_explicador_padrao' => 'https://cdn.quasar.dev/img/avatar3.jpg',
            'capa_padrao' => 'https://cdn.quasar.dev/img/mountains.jpg'
        ]);

        $textos = ConfiguracaoSistema::get('textos', []);
        $titulos = ConfiguracaoSistema::get('titulos', []);
        $subtitulos = ConfiguracaoSistema::get('subtitulos', []);
        $mensagens = ConfiguracaoSistema::get('mensagens', []);

        return response()->json([
            'urls' => $urls,
            'textos' => $textos,
            'titulos' => $titulos,
            'subtitulos' => $subtitulos,
            'mensagens' => $mensagens
        ]);
    }
}
