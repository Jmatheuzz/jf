<?php

namespace App\Services;

use App\Models\ProcessoHabitacional;

class TimelineService
{
    const STATUS_CONCLUIDA = 'CONCLUIDA';
    const STATUS_EM_ANDAMENTO = 'EM_ANDAMENTO';
    const STATUS_PENDENTE = 'PENDENTE';


    public static function montarTimeline(string $etapaAtual): array
    {
        $timeline = [];
        $todasEtapas = ProcessoHabitacional::$etapas;

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
