<?php

namespace App\Services;

use App\Models\ProcessoHabitacional;
use App\Models\User;
use App\Models\Visita;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricaService
{
    /**
     * Calcula a Taxa de Conversão Geral (Processos / Visitas).
     * @return float
     */
    public function getTaxaConversao(): float
    {
        $totalProcessos = ProcessoHabitacional::count();
        $totalVisitas = Visita::count();

        if ($totalVisitas == 0) {
            return 0.0;
        }

        return (float) $totalProcessos / $totalVisitas;
    }

    /**
     * Retorna a quantidade total de clientes.
     * @return int
     */
    public function getQuantidadeClientes(): int
    {
        return User::where('role', 'CLIENTE')->count();
    }

    /**
     * Retorna a quantidade de processos agrupados por etapa (equivalente a countProcessosByEtapa).
     * @return array
     */
    public function getQuantidadeProcessoPorEtapa(): array
    {
        return ProcessoHabitacional::select('etapa', DB::raw('count(*) as quantidade'))
            ->groupBy('etapa')
            ->orderByDesc('quantidade')
            ->get()
            ->pluck('quantidade', 'etapa')
            ->toArray();
    }

    /**
     * Calcula o Tempo Médio do Processo (em dias).
     * (Equivalente a findCreationAndCompletionDates e calcularTempoMedioPorProcessoEmDias)
     * @return float
     */
    public function getTempoMedioProcesso(): float
    {
        // 1. Busca processos que foram concluídos (onde updated_at indica conclusão)
        $processosConcluidos = ProcessoHabitacional::where('etapa', 'FINALIZADO') // Assumindo 'FINALIZADO'
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($processosConcluidos->isEmpty()) {
            return 0.0;
        }

        $totalSegundos = 0;

        foreach ($processosConcluidos as $processo) {
            // Carbon é a biblioteca de datas do Laravel, que facilita o cálculo
            $createdAt = Carbon::parse($processo->created_at);
            $updatedAt = Carbon::parse($processo->updated_at);

            // Calcula a diferença em segundos (equivalente a Duration.between().getSeconds())
            $totalSegundos += $updatedAt->diffInSeconds($createdAt);
        }

        $mediaSegundos = $totalSegundos / $processosConcluidos->count();

        // Converte segundos para dias
        return $mediaSegundos / (60 * 60 * 24);
    }

    /**
     * Lista processos em risco por inatividade.
     * @param int $diasInatividade
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function listarProcessosEmRisco(int $diasInatividade = 30)
    {
        $limiteData = Carbon::now()->subDays($diasInatividade);

        return ProcessoHabitacional::whereNotIn('etapa', ['FINALIZADO', 'CANCELADO']) // Exclui finalizados/cancelados
            ->where('updated_at', '<', $limiteData)
            ->get();
    }

    // app/Services/MetricaService.php

// ... (métodos anteriores)

/**
 * Gera o Ranking de Corretores por Performance (CORRIGIDO).
 * Conta visitas ligando Visita -> Processo -> Corretor.
 * @return array
 */
public function getRankingCorretores(): array
{
    // 1. Contagem de Visitas por Corretor (CORRIGIDO)
    // Faz o JOIN para ligar a visita ao corretor através do processo.
    $mapaVisitas = Visita::select('processos_habitacionais.corretor_id', DB::raw('count(visitas.id) as visitas'))
        ->join('processos_habitacionais', 'visitas.processo_id', '=', 'processos_habitacionais.id')
        ->groupBy('processos_habitacionais.corretor_id')
        ->pluck('visitas', 'corretor_id'); // [corretor_id => count]

    // 2. Contagem de Processos por Corretor (MANTIDO - já estava correto)
    $mapaProcessos = ProcessoHabitacional::select('corretor_id', DB::raw('count(*) as processos'))
        ->groupBy('corretor_id')
        ->pluck('processos', 'corretor_id'); // [corretor_id => count]

    // 3. Mapeamento e Consolidação (MANTIDO)
    $corretores = User::query()->where('role', 'CORRETOR')->get(['id', 'name'])->keyBy('id');

    $ranking = [];

    // Consolida os IDs de todos os envolvidos
    $todosCorretorIds = $corretores->keys()->merge($mapaVisitas->keys())->merge($mapaProcessos->keys())->unique();

    foreach ($todosCorretorIds as $id) {
        // Pega o corretor pelo ID. Se não existir (dados sujos), pula.
        $corretor = $corretores->get($id);
        if (!$corretor) continue;
        
        $visitas = $mapaVisitas->get($id, 0);
        $processos = $mapaProcessos->get($id, 0);

        $taxa = ($visitas > 0) ? ($processos / $visitas) * 100.0 : 0.0;

        $ranking[] = [
            'corretorId' => $id,
            'nomeCorretor' => $corretor->name,
            'visitasAgendadas' => $visitas,
            'processosIniciados' => $processos,
            'taxaConversao' => round($taxa, 2),
        ];
    }

    // Ordena pela taxa de conversão (decrescente)
    usort($ranking, fn ($a, $b) => $b['taxaConversao'] <=> $a['taxaConversao']);

    return $ranking;
}

    /**
     * Gera o Pipeline Detalhado de Processos por Corretor.
     * (Equivalente a getPipelineProcessosPorCorretor)
     * @return array
     */
    public function getPipelineProcessosPorCorretor(): array
    {
        $corretores = User::query()->where('role', 'CORRETOR')->get(['id', 'name']);
        $pipeline = [];

        foreach ($corretores as $corretor) {
            // Busca processos em andamento
            $processosEmAndamento = ProcessoHabitacional::with('cliente') // Carrega o cliente para pegar o nome
                ->where('corretor_id', $corretor->id)
                ->whereNotIn('etapa', ['FINALIZADO', 'CANCELADO'])
                ->get();

            // Mapeia para o formato DTO desejado
            $processosDTO = $processosEmAndamento->map(function ($processo) {
                return [
                    'id' => $processo->id,
                    'clienteNome' => $processo->cliente->nome ?? 'Cliente Desconhecido',
                    'etapaAtual' => $processo->etapa,
                ];
            })->toArray(); // Converte a Collection de volta para array

            $pipeline[] = [
                'corretorId' => $corretor->id,
                'nomeCorretor' => $corretor->nome,
                'totalProcessosEmAndamento' => count($processosDTO),
                'processos' => $processosDTO,
            ];
        }

        return $pipeline;
    }
}