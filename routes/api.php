<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EstudanteController;
use App\Http\Controllers\Api\ExplicadorController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AvaliacaoController;
use App\Http\Controllers\Api\ConfiguracaoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ==================== ROTAS PÚBLICAS ====================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// ==================== CONFIGURAÇÕES (públicas) ====================
Route::prefix('configuracoes')->group(function () {
    Route::get('/materias', [ConfiguracaoController::class, 'getMaterias']);
    Route::get('/tipos-servico', [ConfiguracaoController::class, 'getTiposServico']);
    Route::get('/niveis-ensino', [ConfiguracaoController::class, 'getNiveisEnsino']);
    Route::get('/prazos', [ConfiguracaoController::class, 'getPrazos']);
    Route::get('/sistema', [ConfiguracaoController::class, 'getSistema']);
});

// ==================== INSTITUIÇÕES (públicas) ====================
Route::prefix('instituicoes')->group(function () {
    Route::get('/proximas', [ConfiguracaoController::class, 'getInstituicoesProximas']);
    Route::get('/busca', [ConfiguracaoController::class, 'buscarInstituicoes']);
});

// ==================== ROTAS PROTEGIDAS ====================
Route::middleware('auth:sanctum')->group(function () {

    // ===== AUTENTICAÇÃO =====
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/perfil', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // ===== AVALIAÇÕES =====
    Route::prefix('avaliacoes')->group(function () {
        Route::get('/recebidas', [AvaliacaoController::class, 'recebidas']);
        Route::get('/enviadas', [AvaliacaoController::class, 'enviadas']);
        Route::get('/pode-avaliar/{pedidoId}', [AvaliacaoController::class, 'podeAvaliar']);
        Route::post('/{id}/responder', [AvaliacaoController::class, 'responder']);
        Route::get('/explicador/{id}/estatisticas', [AvaliacaoController::class, 'estatisticasExplicador']);
        Route::get('/{id}', [AvaliacaoController::class, 'show']);
    });

    // ===== ROTAS DO ESTUDANTE =====
    Route::prefix('estudante')->group(function () {

        // Pedidos
        Route::get('/pedidos', [EstudanteController::class, 'getPedidos']);
        Route::post('/pedidos', [EstudanteController::class, 'criarPedido']);
        Route::get('/pedidos/{id}', [EstudanteController::class, 'verPedido']);
        Route::delete('/pedidos/{id}', [EstudanteController::class, 'cancelarPedido']);

        // Propostas
        Route::post('/propostas/{id}/aceitar', [EstudanteController::class, 'aceitarProposta']);

        // Avaliações
        Route::post('/sessoes/{id}/avaliar', [EstudanteController::class, 'avaliarSessao']);

        // Biblioteca
        Route::get('/biblioteca', [EstudanteController::class, 'getBiblioteca']);
        Route::patch('/biblioteca/{id}/favorito', [EstudanteController::class, 'toggleFavorito']);
        Route::patch('/biblioteca/{id}/progresso', [EstudanteController::class, 'atualizarProgresso']);

        // Conteúdos (Loja)
        Route::get('/conteudos', [EstudanteController::class, 'getConteudos']);
        Route::get('/conteudos/{id}', [EstudanteController::class, 'verConteudo']);
        Route::post('/conteudos/{id}/comprar', [EstudanteController::class, 'comprarConteudo']);

        // Carrinho
        Route::get('/carrinho', [EstudanteController::class, 'getCarrinho']);
        Route::post('/carrinho', [EstudanteController::class, 'adicionarAoCarrinho']);
        Route::delete('/carrinho/{id}', [EstudanteController::class, 'removerDoCarrinho']);
        Route::post('/carrinho/finalizar', [EstudanteController::class, 'finalizarCompra']);

        // Feedbacks
        Route::get('/meus-feedbacks', [EstudanteController::class, 'getMeusFeedbacks']);
        Route::post('/feedbacks', [EstudanteController::class, 'enviarFeedback']);
    });

    // ===== ROTAS DO EXPLICADOR =====
    Route::prefix('explicador')->group(function () {

        // Pedidos Disponíveis
        Route::get('/pedidos-disponiveis', [ExplicadorController::class, 'getPedidosDisponiveis']);
        Route::post('/pedidos/{id}/proposta', [ExplicadorController::class, 'fazerProposta']);
        Route::post('/pedidos/{id}/aceitar', [ExplicadorController::class, 'aceitarPedido']);

        // Minhas Propostas
        Route::get('/minhas-propostas', [ExplicadorController::class, 'getMinhasPropostas']);

        // Meus Pedidos (ativos e concluídos)
        Route::get('/meus-pedidos', [ExplicadorController::class, 'getMeusPedidos']);
        Route::post('/pedidos/{id}/iniciar', [ExplicadorController::class, 'iniciarSessao']);
        Route::post('/pedidos/{id}/concluir', [ExplicadorController::class, 'concluirPedido']);
        Route::post('/pedidos/{id}/agendar', [ExplicadorController::class, 'agendarSessao']);

        // Cancelar proposta
        Route::delete('/propostas/{id}', [ExplicadorController::class, 'cancelarProposta']);

        // Meus Conteúdos
        Route::get('/meus-conteudos', [ExplicadorController::class, 'getMeusConteudos']);
        Route::post('/conteudos', [ExplicadorController::class, 'publicarConteudo']);
        Route::put('/conteudos/{id}', [ExplicadorController::class, 'editarConteudo']);
        Route::patch('/conteudos/{id}/status', [ExplicadorController::class, 'toggleConteudoStatus']);
        Route::get('/conteudos/{id}/estatisticas', [ExplicadorController::class, 'estatisticasConteudo']);

        // Ganhos
        Route::get('/ganhos', [ExplicadorController::class, 'getGanhos']);
        Route::post('/saque', [ExplicadorController::class, 'solicitarSaque']);
    });

    // ===== ROTAS DO ADMIN (protegidas pelo middleware admin) =====
    Route::prefix('admin')->middleware('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Usuários
        Route::get('/usuarios', [AdminController::class, 'getUsuarios']);
        Route::get('/usuarios/{id}', [AdminController::class, 'getUsuario']);
        Route::put('/usuarios/{id}', [AdminController::class, 'atualizarUsuario']);
        Route::patch('/usuarios/{id}/status', [AdminController::class, 'toggleUsuarioStatus']);

        // Explicadores
        Route::get('/explicadores', [AdminController::class, 'getExplicadores']);
        Route::post('/explicadores/{id}/aprovar', [AdminController::class, 'aprovarExplicador']);
        Route::post('/explicadores/{id}/reprovar', [AdminController::class, 'reprovarExplicador']);

        // Conteúdos
        Route::get('/conteudos', [AdminController::class, 'getConteudos']);
        Route::post('/conteudos/{id}/aprovar', [AdminController::class, 'aprovarConteudo']);
        Route::post('/conteudos/{id}/reprovar', [AdminController::class, 'reprovarConteudo']);
        Route::delete('/conteudos/{id}', [AdminController::class, 'removerConteudo']);

        // Transações
        Route::get('/transacoes', [AdminController::class, 'getTransacoes']);
        Route::get('/resumo-financeiro', [AdminController::class, 'getResumoFinanceiro']);

        // Feedbacks
        Route::get('/feedbacks', [AdminController::class, 'getFeedbacks']);
        Route::post('/feedbacks/{id}/responder', [AdminController::class, 'responderFeedback']);
        Route::patch('/feedbacks/{id}/resolver', [AdminController::class, 'resolverFeedback']);
    });
});

// ==================== ROTA DE TESTE ====================
Route::get('/test', function() {
    return response()->json([
        'message' => 'API EduLink funcionando!',
        'version' => '1.0.0',
        'timestamp' => now()->toDateTimeString()
    ]);
});
