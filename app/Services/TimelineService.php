<?php

namespace App\Services;

class TimelineService
{
    const STATUS_CONCLUIDA = 'CONCLUIDA';
    const STATUS_EM_ANDAMENTO = 'EM_ANDAMENTO';
    const STATUS_PENDENTE = 'PENDENTE';

    private static $etapasPre = [
        'COLETA_DOCUMENTACAO' => 'Coleta de Documentação',
        'ANALISE_CREDITO' => 'Análise de Crédito',
        'RESERVA' => 'Reserva do Imóvel',
    ];

    private static $etapasProcesso = [
        'CONTRATO_EMPREITADA' => 'Contrato de Empreitada',
        'CONFECCAO_PROJETO' => 'Confecção do Projeto',
        'ENTREGA_PREFEITURA' => 'Entrega na Prefeitura',
        'ANALISE_CREDITO_CAIXA' => 'Análise de Crédito Caixa',
        'AVALIACAO_IMOVEL_CAIXA' => 'Avaliação do Imóvel Caixa',
        'ASSINATURA_CONTRATO' => 'Assinatura do Contrato',
        'REGISTRO_CARTORIO' => 'Registro em Cartório',
        'FINALIZADO' => 'Processo Finalizado',
    ];

    public static function montarTimeline(string $etapaAtual): array
    {
        $timeline = [];
        $todasEtapas = array_merge(self::$etapasPre, self::$etapasProcesso);

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
