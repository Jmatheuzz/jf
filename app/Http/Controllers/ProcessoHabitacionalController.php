<?php
namespace App\Http\Controllers;

use App\Models\ProcessoHabitacional;
use App\Models\ProcessoHabitacionalHistory;
use App\Services\TimelineService;
use Illuminate\Http\Request;

class ProcessoHabitacionalController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcessoHabitacional::with(['cliente', 'imovel', 'corretor']);

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('processo_id')) {
            $query->where('id', $request->processo_id);
        }


        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => 'required|integer',
            'corretor_id' => 'nullable|integer',
            'imovel_id' => 'nullable|integer',
        ]);

        $processo = ProcessoHabitacional::create($data);
        return response()->json($processo, 201);
    }

    public function show($id)
    {
        $processo = ProcessoHabitacional::with(['cliente', 'imovel', 'corretor'])->findOrFail($id);

        $timeline = TimelineService::montarTimeline($processo->etapa);

        return response()->json([
            'processo' => $processo,
            'timeline' => $timeline
        ]);
    }

    public function update(Request $request, $id)
    {
        $processo = ProcessoHabitacional::findOrFail($id);
        $processo->update($request->all());
        return response()->json($processo);
    }

    public function destroy($id)
    {
        ProcessoHabitacional::destroy($id);
        return response()->json(null, 204);
    }

    public function avancarEtapa($id)
    {
        $processo = ProcessoHabitacional::findOrFail($id);
        $etapaAnterior = $processo->etapa;

        $novaEtapa = $processo->getProximaEtapa();

        if ($novaEtapa === $etapaAnterior) {
            return response()->json(['message' => 'O processo já está na última etapa.'], 400);
        }

        $processo->update(['etapa' => $novaEtapa]);

        ProcessoHabitacionalHistory::create([
            'processo_id' => $processo->id,
            'etapa'       => $novaEtapa,
            'observacao'  => "Avançou da etapa {$etapaAnterior} para {$novaEtapa} via API"
        ]);

        return response()->json([
            'message' => 'Etapa avançada com sucesso!',
            'etapa' => $novaEtapa,
            'descricao' => ProcessoHabitacional::$etapas[$novaEtapa] ?? null,
        ]);
    }

    /**
     * 🔙 Retorna o processo para a etapa anterior
     */
    public function voltarEtapa($id)
    {
        $processo = ProcessoHabitacional::findOrFail($id);
        $etapaAnterior = $processo->etapa;

        $novaEtapa = $processo->getEtapaAnterior();

        if ($novaEtapa === $etapaAnterior) {
            return response()->json(['message' => 'O processo já está na primeira etapa.'], 400);
        }

        $processo->update(['etapa' => $novaEtapa]);

        ProcessoHabitacionalHistory::create([
            'processo_id' => $processo->id,
            'etapa'       => $novaEtapa,
            'observacao'  => "Retrocedeu da etapa {$etapaAnterior} para {$novaEtapa} via API"
        ]);

        return response()->json([
            'message' => 'Etapa retornada com sucesso!',
            'etapa' => $novaEtapa,
            'descricao' => ProcessoHabitacional::$etapas[$novaEtapa] ?? null,
        ]);
    }

    public function adicionarImovel(Request $request, $id)
    {
        $processo = ProcessoHabitacional::findOrFail($id);
        $request->validate(['imovel_id' => 'required|integer']);
        $processo->imovel_id = $request->imovel_id;
        $processo->save();

        return response()->json(['message' => 'Imóvel adicionado ao processo com sucesso']);
    }
}
