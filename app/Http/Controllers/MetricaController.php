<?php

namespace App\Http\Controllers;

use App\Services\MetricaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Importante: use o nome do seu Controller. 
// Se o seu Controller do Java estava em /auth, você deve mapear isso nas rotas.

class MetricaController extends Controller
{
    protected $metricaService;

    public function __construct(MetricaService $metricaService)
    {
        $this->metricaService = $metricaService;
    }

    public function taxaConversao(): JsonResponse
    {
        $value = $this->metricaService->getTaxaConversao();
        // Laravel retorna JSON automaticamente, mas vamos simular a ResponseOk do Java
        return response()->json([
            'status' => 'Ok',
            'valor' => $value
        ], Response::HTTP_OK);
    }

    public function quantidadeClientes(): JsonResponse
    {
        $value = $this->metricaService->getQuantidadeClientes();
        return response()->json([
            'status' => 'Ok',
            'valor' => $value
        ], Response::HTTP_OK);
    }

    public function quantidadeProcessoPorEtapa(): JsonResponse
    {
        $value = $this->metricaService->getQuantidadeProcessoPorEtapa();
        return response()->json([
            'status' => 'Ok',
            'valor' => $value
        ], Response::HTTP_OK);
    }

    public function tempoMedioProcesso(): JsonResponse
    {
        $value = $this->metricaService->getTempoMedioProcesso();
        return response()->json([
            'status' => 'Ok',
            'valor' => $value
        ], Response::HTTP_OK);
    }

    public function processosRisco(Request $request): JsonResponse
    {
        // Pega os dias de inatividade da requisição, ou usa 30 como padrão
        $diasInatividade = $request->get('dias', 30); 
        $risco = $this->metricaService->listarProcessosEmRisco((int) $diasInatividade);
        
        // Retorna a lista de ProcessoHabitacional como array JSON
        return response()->json($risco, Response::HTTP_OK);
    }

    public function rankingCorretores(): JsonResponse
    {
        $ranking = $this->metricaService->getRankingCorretores();
        return response()->json($ranking, Response::HTTP_OK);
    }

    public function pipelineCorretores(): JsonResponse
    {
        $pipeline = $this->metricaService->getPipelineProcessosPorCorretor();
        return response()->json($pipeline, Response::HTTP_OK);
    }
}