<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Materia;
use App\Models\TipoServico;
use App\Models\NivelEnsino;
use App\Models\PrazoEntrega;
use App\Models\InstituicaoEnsino;
use App\Models\ConfiguracaoSistema;
use App\Models\DialogoSistema;

class ConfiguracoesSeeder extends Seeder
{
    public function run()
    {
        // Matérias
        $materias = [
            ['nome' => 'Matemática', 'icone' => 'functions', 'cor' => 'primary', 'ordem' => 1],
            ['nome' => 'Física', 'icone' => 'science', 'cor' => 'primary', 'ordem' => 2],
            ['nome' => 'Química', 'icone' => 'biotech', 'cor' => 'primary', 'ordem' => 3],
            ['nome' => 'Português', 'icone' => 'translate', 'cor' => 'primary', 'ordem' => 4],
            ['nome' => 'História', 'icone' => 'history', 'cor' => 'primary', 'ordem' => 5],
            ['nome' => 'Geografia', 'icone' => 'public', 'cor' => 'primary', 'ordem' => 6],
            ['nome' => 'Biologia', 'icone' => 'pets', 'cor' => 'primary', 'ordem' => 7],
            ['nome' => 'Inglês', 'icone' => 'language', 'cor' => 'primary', 'ordem' => 8],
        ];

        foreach ($materias as $materia) {
            Materia::create($materia);
        }

        // Tipos de Serviço
        $tiposServico = [
            [
                'nome' => 'Sessão ao vivo',
                'icone' => 'videocam',
                'descricao' => 'Aula em tempo real com o explicador',
                'ordem' => 1
            ],
            [
                'nome' => 'Resolução de exercícios',
                'icone' => 'edit_note',
                'descricao' => 'Exercícios resolvidos passo a passo',
                'ordem' => 2
            ],
            [
                'nome' => 'Revisão de trabalho',
                'icone' => 'rate_review',
                'descricao' => 'Correção e feedback de trabalhos',
                'ordem' => 3
            ],
            [
                'nome' => 'Preparação para teste',
                'icone' => 'quiz',
                'descricao' => 'Revisão para provas e exames',
                'ordem' => 4
            ],
        ];

        foreach ($tiposServico as $tipo) {
            TipoServico::create($tipo);
        }

        // Níveis de Ensino
        $niveis = [
            ['nome' => 'Ensino Secundário', 'descricao' => '8ª a 12ª classe', 'ordem' => 1],
            ['nome' => 'Ensino Técnico', 'descricao' => 'Cursos técnicos e profissionais', 'ordem' => 2],
            ['nome' => 'Ensino Superior', 'descricao' => 'Universidade e faculdade', 'ordem' => 3],
        ];

        foreach ($niveis as $nivel) {
            NivelEnsino::create($nivel);
        }

        // Prazos de Entrega
        $prazos = [
            [
                'nome' => 'Hoje',
                'icone' => 'whatshot',
                'dias' => 0,
                'prioridade' => 1,
                'permite_mapa' => false
            ],
            [
                'nome' => 'Amanhã',
                'icone' => 'schedule',
                'dias' => 1,
                'prioridade' => 2,
                'permite_mapa' => false
            ],
            [
                'nome' => 'Esta semana',
                'icone' => 'calendar_today',
                'dias' => 7,
                'prioridade' => 3,
                'permite_mapa' => false
            ],
            [
                'nome' => 'Sem urgência',
                'icone' => 'spa',
                'dias' => null,
                'prioridade' => 4,
                'permite_mapa' => false
            ],
            [
                'nome' => 'Selecionar no mapa',
                'icone' => 'map',
                'dias' => null,
                'prioridade' => 5,
                'permite_mapa' => true
            ],
        ];

        foreach ($prazos as $prazo) {
            PrazoEntrega::create($prazo);
        }

        // Instituições de Ensino (Maputo)
        $instituicoes = [
            [
                'nome' => 'Escola Secundária da Matola',
                'tipo' => 'escola',
                'nivel_ensino_id' => 1,
                'endereco' => 'Av. Principal, Matola',
                'latitude' => -25.9621,
                'longitude' => 32.4586,
                'telefone' => '84 123 4567'
            ],
            [
                'nome' => 'Instituto Industrial de Maputo',
                'tipo' => 'instituto_tecnico',
                'nivel_ensino_id' => 2,
                'endereco' => 'Av. 24 de Julho, Maputo',
                'latitude' => -25.9689,
                'longitude' => 32.5736,
                'telefone' => '84 123 4568'
            ],
            [
                'nome' => 'Universidade Eduardo Mondlane',
                'tipo' => 'faculdade',
                'nivel_ensino_id' => 3,
                'endereco' => 'Campus Universitário, Maputo',
                'latitude' => -25.9533,
                'longitude' => 32.6029,
                'telefone' => '84 123 4569'
            ],
        ];

        foreach ($instituicoes as $inst) {
            InstituicaoEnsino::create($inst);
        }

        // Configurações do Sistema
        ConfiguracaoSistema::set('urls', [
            'avatar_aluno_padrao' => 'https://cdn.quasar.dev/img/avatar2.jpg',
            'avatar_explicador_padrao' => 'https://cdn.quasar.dev/img/avatar3.jpg',
            'capa_padrao' => 'https://cdn.quasar.dev/img/mountains.jpg'
        ], 'json', 'URLs padrão para imagens');

        ConfiguracaoSistema::set('textos', [
            'filtros' => [
                'todos' => 'Todos',
                'ativos' => 'Ativos',
                'concluidos' => 'Concluídos'
            ],
            'botoes' => [
                'novoPedido' => '+ Novo Pedido',
                'criarPrimeiroPedido' => 'Criar primeiro pedido',
                'aceitarProposta' => 'Aceitar proposta',
                'detalhes' => 'Detalhes',
                'enviar' => 'Enviar',
                'verDetalhes' => 'Ver detalhes',
                'explorarConteudos' => 'Explorar conteúdos',
                'comprar' => 'Comprar',
                'cancelar' => 'Cancelar',
                'publicar' => 'Publicar',
                'ok' => 'OK',
                'selecionarNoMapa' => 'Selecionar no mapa',
                'confirmarLocalizacao' => 'Confirmar localização'
            ],
            'formulario' => [
                'materia' => 'Matéria *',
                'titulo' => 'Título *',
                'descricao' => 'Descrição *',
                'tipoServico' => 'Tipo de serviço *',
                'nivel' => 'Nível *',
                'duracao' => 'Duração *',
                'prazo' => 'Prazo *',
                'local' => 'Local *',
                'urgente' => 'É urgente?',
                'valor' => 'Valor (MZN) *',
                'digiteEndereco' => 'Digite o endereço',
                'outro' => 'Outro',
                'nome' => 'Nome completo'
            ],
            'menu' => [
                'editarPerfil' => 'Editar Perfil',
                'metodosPagamento' => 'Métodos de Pagamento',
                'configuracoes' => 'Configurações',
                'ajuda' => 'Ajuda e Suporte',
                'sair' => 'Sair'
            ]
        ], 'json', 'Textos da interface');

        ConfiguracaoSistema::set('titulos', [
            'biblioteca' => 'Minha Biblioteca',
            'loja' => 'Loja de Conteúdos',
            'perfil' => 'Perfil'
        ], 'json', 'Títulos das páginas');

        ConfiguracaoSistema::set('subtitulos', [
            'loja' => 'Aprenda com os melhores'
        ], 'json', 'Subtítulos');

        ConfiguracaoSistema::set('mensagens', [
            'vazio' => [
                'pedidos' => 'Nenhum pedido encontrado',
                'biblioteca' => 'Sua biblioteca está vazia',
                'loja' => 'Nenhum conteúdo encontrado'
            ],
            'emDesenvolvimento' => 'Função em desenvolvimento',
            'semNotificacoes' => 'Você não tem notificações no momento'
        ], 'json', 'Mensagens do sistema');

        // Diálogos do Sistema
        $dialogos = [
            [
                'tipo' => 'confirmacao',
                'acao' => 'aceitar_proposta',
                'titulo' => 'Confirmar',
                'mensagem' => 'Deseja aceitar esta proposta?',
                'botao_principal_texto' => 'Aceitar',
                'botao_secundario_texto' => 'Cancelar'
            ],
            [
                'tipo' => 'confirmacao',
                'acao' => 'comprar_conteudo',
                'titulo' => 'Confirmar compra',
                'mensagem' => 'Deseja comprar este conteúdo?',
                'botao_principal_texto' => 'Comprar',
                'botao_secundario_texto' => 'Cancelar'
            ],
            [
                'tipo' => 'confirmacao',
                'acao' => 'logout',
                'titulo' => 'Sair',
                'mensagem' => 'Deseja realmente sair?',
                'botao_principal_texto' => 'Sair',
                'botao_principal_cor' => 'negative',
                'botao_secundario_texto' => 'Cancelar'
            ],
            [
                'tipo' => 'erro',
                'acao' => 'erro_padrao',
                'titulo' => 'Erro',
                'mensagem' => 'Ocorreu um erro. Tente novamente.',
                'botao_principal_texto' => 'OK',
                'botao_secundario_texto' => ''
            ],
            [
                'tipo' => 'info',
                'acao' => 'extrato_saldo',
                'titulo' => 'Extrato de Saldo',
                'mensagem' => '',
                'botao_principal_texto' => 'Fechar',
                'botao_secundario_texto' => ''
            ],
            [
                'tipo' => 'info',
                'acao' => 'notificacoes',
                'titulo' => 'Notificações',
                'mensagem' => 'Você não tem notificações no momento',
                'botao_principal_texto' => 'OK',
                'botao_secundario_texto' => ''
            ],
            [
                'tipo' => 'info',
                'acao' => 'metodos_pagamento',
                'titulo' => 'Métodos de Pagamento',
                'mensagem' => '',
                'botao_principal_texto' => 'Fechar',
                'botao_secundario_texto' => ''
            ],
            [
                'tipo' => 'info',
                'acao' => 'configuracoes',
                'titulo' => 'Configurações',
                'mensagem' => 'Função em desenvolvimento',
                'botao_principal_texto' => 'OK',
                'botao_secundario_texto' => ''
            ],
            [
                'tipo' => 'info',
                'acao' => 'ajuda',
                'titulo' => 'Ajuda e Suporte',
                'mensagem' => 'suporte@edulink.co.mz',
                'botao_principal_texto' => 'OK',
                'botao_secundario_texto' => ''
            ],
            [
                'tipo' => 'confirmacao',
                'acao' => 'novo_pedido',
                'titulo' => 'Novo Pedido de Ajuda',
                'mensagem' => '',
                'botao_principal_texto' => 'Publicar',
                'botao_secundario_texto' => 'Cancelar'
            ],
            [
                'tipo' => 'info',
                'acao' => 'detalhes_pedido',
                'titulo' => 'Detalhes do Pedido',
                'mensagem' => '',
                'botao_principal_texto' => 'Fechar',
                'botao_secundario_texto' => ''
            ],
            [
                'tipo' => 'info',
                'acao' => 'mapa',
                'titulo' => 'Selecionar no mapa',
                'mensagem' => '',
                'botao_principal_texto' => 'Confirmar localização',
                'botao_secundario_texto' => 'Cancelar'
            ],
        ];

        // foreach ($dialogos as $dialogo) {
        //     DialogoSistema::create($dialogo);
        // }
    }
}
