<?php

namespace App\Services;

use App\Models\Atendimento;
use App\Models\ProcessoHabitacional;

class TimelineService
{
    const STATUS_CONCLUIDA = 'CONCLUIDA';
    const STATUS_EM_ANDAMENTO = 'EM_ANDAMENTO';
    const STATUS_PENDENTE = 'PENDENTE';
    public static function montarTimeline(string $etapaAtual, string $statusEtapaAtual): array
    {
        $timeline = [];
        $todasEtapas = ProcessoHabitacional::$etapas;

        $etapaEncontrada = false;

        foreach ($todasEtapas as $chave => $descricao) {
            if ($chave === $etapaAtual) {
                $status = $statusEtapaAtual;
                $etapaEncontrada = true;
            } elseif (!$etapaEncontrada) {
                $status = ProcessoHabitacional::STATUS_CONCLUIDA;
            } else {
                $status = ProcessoHabitacional::STATUS_PENDENTE;
            }

            $timeline[] = [
                'chave' => $chave,
                'descricao' => $descricao,
                'status' => $status
            ];
        }

        return $timeline;
    }

    public static function montarTimelineAtendimento(string $etapaAtual): array
    {
        $timeline = [];
        $todasEtapas = Atendimento::$etapas;

        if ($etapaAtual === 'FINALIZADO') {
            foreach ($todasEtapas as $chave => $descricao) {
                $timeline[] = [
                    'chave' => $chave,
                    'descricao' => $descricao,
                    'status' => self::STATUS_CONCLUIDA
                ];
            }
            return $timeline;
        }

        $etapaEncontrada = false;

        foreach ($todasEtapas as $chave => $descricao) {
            if (!$etapaEncontrada) {
                if ($chave === $etapaAtual) {
                    $status = self::STATUS_EM_ANDAMENTO;
                    $etapaEncontrada = true;
                } else {
                    $status = self::STATUS_CONCLUIDA;
                }
            } else {
                $status = self::STATUS_PENDENTE;
            }

            $timeline[] = [
                'chave' => $chave,
                'descricao' => $descricao,
                'status' => $status
            ];
        }

        return $timeline;
    }
}
