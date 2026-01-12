<?php

use App\Http\Controllers\AtendimentoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComissaoController;
use App\Http\Controllers\FotoImovelController;
use App\Http\Controllers\ImovelController;
use App\Http\Controllers\MetricaController;
use App\Http\Controllers\PosVendaController;
use App\Http\Controllers\ProcessoHabitacionalController;
use App\Http\Controllers\ProcessoHabitacionalHistoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitaController;
use App\Http\Controllers\DocumentoProcessoHabitacionalController;
use App\Models\Atendimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware(['api'])->prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::post('validation-email-signup', 'validateEmail');
    Route::post('password/request', [AuthController::class, 'requestPasswordReset']);
    Route::post('password/validate', [AuthController::class, 'validatePasswordResetCode']);
    Route::post('password/reset', [AuthController::class, 'resetPassword']);
});

Route::get('imoveis/public', [ImovelController::class, 'indexPublic']);
Route::get('imoveis/public/{id}', [ImovelController::class, 'show']);

Route::middleware(['jwt.verify'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::apiResource('imoveis', ImovelController::class);
    Route::apiResource('fotos-imovel', FotoImovelController::class);

    Route::post('fotos-imovel/delete-by-path', [FotoImovelController::class, 'destroyByPath']);
    Route::post('fotos-imovel/multiplas', [FotoImovelController::class, 'storeMultiple']);

    Route::apiResource('processos', ProcessoHabitacionalController::class);
    Route::apiResource('atendimentos', AtendimentoController::class);
    Route::apiResource('processos-historico', ProcessoHabitacionalHistoryController::class);
    Route::apiResource('pos-vendas', PosVendaController::class);
    Route::apiResource('users', UserController::class);
    Route::get('users/{user}/processos', [UserController::class, 'getProcessosByCorretor']);
    Route::apiResource('visitas', VisitaController::class);
    Route::get('comissoes/previsao', [ComissaoController::class, 'previsao']);
    Route::apiResource('comissoes', ComissaoController::class);

    // Documentos do processo habitacional
    Route::post('processos/{processo_id}/documentos', [DocumentoProcessoHabitacionalController::class, 'store']);
    Route::delete('documentos/{id}', [DocumentoProcessoHabitacionalController::class, 'destroy']);


    Route::get('corretores', [UserController::class, 'getCorretores']);
    Route::get('clientes', [UserController::class, 'getClientes']);

    // Processos habitacionais — avançar/voltar etapa e adicionar imóvel
    Route::post('processos/{id}/proxima-etapa', [ProcessoHabitacionalController::class, 'avancarEtapa']);
    Route::post('processos/{id}/avancar-etapa-esteira', [ProcessoHabitacionalController::class, 'avancarEtapaEsteira']);
    Route::post('processos/{id}/etapa-anterior', [ProcessoHabitacionalController::class, 'voltarEtapa']);
    Route::post('processos/{id}/adicionar-imovel', [ProcessoHabitacionalController::class, 'adicionarImovel']);
    Route::post('processos/grouped-by-etapa', [ProcessoHabitacionalController::class, 'groupedByEtapa']);

    Route::post('atendimentos/grouped-by-etapa', [AtendimentoController::class, 'groupedByEtapa']);
    Route::post('atendimentos/{id}/proxima-etapa', [AtendimentoController::class, 'avancarEtapa']);
    Route::post('atendimentos/{id}/etapa-anterior', [AtendimentoController::class, 'voltarEtapa']);
    Route::post('atendimentos/{id}/adicionar-imovel', [AtendimentoController::class, 'adicionarImovel']);
     

    // Confirmar visita
    Route::post('visitas/confirmar/{id}', [VisitaController::class, 'confirmar']);

    Route::get('taxa-conversao', [MetricaController::class, 'taxaConversao']);
    Route::get('quantidade-clientes', [MetricaController::class, 'quantidadeClientes']);
    Route::get('quantidade-processo-por-etapa', [MetricaController::class, 'quantidadeProcessoPorEtapa']);
    Route::get('tempo-medio-processo', [MetricaController::class, 'tempoMedioProcesso']);
    Route::get('processos-risco', [MetricaController::class, 'processosRisco']);
    Route::get('ranking-corretores', [MetricaController::class, 'rankingCorretores']);
    Route::get('pipeline-corretores', [MetricaController::class, 'pipelineCorretores']);

});
