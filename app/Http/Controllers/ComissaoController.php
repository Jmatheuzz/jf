<?php

namespace App\Http\Controllers;

use App\Models\Comissao;
use App\Models\ProcessoHabitacional;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ComissaoController extends Controller
{

    public function index()
    {
        $comissoes = Comissao::with('processoHabitacional')->get();
        return response()->json($comissoes);
    }

    public function store(Request $request)
    {

        return $this->success('Comissão criada com sucesso', [], 201);
    }

    public function show(string $id)
    {
        $comissao = Comissao::with('processoHabitacional')->find($id);

        if (!$comissao) {
            return $this->error('Comissão não encontrada', 404);
        }

        return $this->success('Comissão encontrada', $comissao);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'pago' => 'boolean',
        ]);

        $comissao = Comissao::find($id);
        
        if (!$comissao) {
            return $this->error('Comissão não encontrada', 404);
        }

        $comissao->update($request->all());

        return response()->json( $comissao);
    }

    public function destroy(string $id)
    {
        $comissao = Comissao::find($id);

        if (!$comissao) {
            return $this->error('Comissão não encontrada', 404);
        }

        $comissao->delete();

        return $this->success('Comissão deletada com sucesso');
    }

    public function previsao(Request $request)
    {
        $processos = ProcessoHabitacional::with('imovel')
            ->whereNotNull('data_assinatura_empreitada')
            ->get();
    
        $previsoes = collect();
        $tz = 'America/Sao_Paulo';
    
        foreach ($processos as $processo) {
            $valorComissao = 0;
            if ($processo->imovel && $processo->imovel->valor > 10000) {
                $valorComissao = ($processo->imovel->valor - 10000) * 0.03;
            }

            $dataAssinatura = Carbon::parse($processo->data_assinatura_empreitada, $tz);
            $dataPagamento = $dataAssinatura->addMonths(4);
    
            if ($dataPagamento->year >= 2026) {
                $previsoes->push([
                    'comissao_calculada' => [
                        'processo_habitacional_id' => $processo->id,
                        'valor' => $valorComissao,
                        'pago' => false,
                        'processo_habitacional' => $processo
                    ],
                    'data_pagamento' => $dataPagamento,
                ]);
            }
        }
    
        $previsoesAgrupadas = $previsoes->groupBy(function ($item) {
            return $item['data_pagamento']->format('Y-m');
        })->sortKeys();
    
        $resultado = $previsoesAgrupadas->map(function ($mesComissoes, $mes) {
            return [
                'mes' => $mes,
                'comissoes' => $mesComissoes->pluck('comissao_calculada'),
                'total_mes' => $mesComissoes->sum(function ($item) {
                    return $item['comissao_calculada']['valor'];
                }),
            ];
        })->values();
    
        $totalGeral = $resultado->sum('total_mes');
    
        return response()->json([
            'previsao_por_mes' => $resultado,
            'total_geral' => $totalGeral,
        ]);
    }
}
