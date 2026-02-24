<?php
namespace App\Http\Controllers;

use App\Models\Imovel;
use App\Models\Atendimento;
use App\Models\AtendimentoHistory;
use App\Services\TimelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtendimentoController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Atendimento::with(['cliente', 'corretor']);

        if ($user->role === 'CORRETOR') {
            $query->where('corretor_id', $user->id);
        } elseif ($user->role === 'CLIENTE') {
            $query->where('cliente_id', $user->id);
        }

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
            'observacao' => 'nullable|string',
            'motivoCancelamento' => 'nullable|string',
            'valor_simulacao' => 'nullable|numeric',
            'data_simulacao' => 'nullable|date',
        ]);
        $data['etapa'] = array_key_first(Atendimento::$etapas);
        $processo = Atendimento::create($data);
        return response()->json($processo, 201);
    }

    public function show($id)
    {
        $user = auth()->user();
        $processo = Atendimento::with(['cliente', 'corretor'])->findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $timeline = TimelineService::montarTimelineAtendimento($processo->etapa);

        return response()->json([
            'processo' => $processo,
            'timeline' => $timeline
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $processo = Atendimento::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->all();
        if ($request->boolean('is_active')) {
            $data['motivoCancelamento'] = null;
        }

        $processo->update($data);
        return response()->json($processo);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $processo = Atendimento::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Atendimento::destroy($id);
        return response()->json(null, 204);
    }

    public function avancarEtapa(Request $request, $id)
    {
        $user = auth()->user();
        $processo = Atendimento::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $etapaAnterior = $processo->etapa;

        $novaEtapa = $processo->getProximaEtapa();

        if ($novaEtapa === $etapaAnterior) {
            return response()->json(['message' => 'O processo já está na última etapa.']);
        }

        $updateData = ['etapa' => $novaEtapa];
        if ($etapaAnterior === 'SIMULACAO') {
            $updateData['data_simulacao'] = now();
            $updateData['valor_simulacao'] = $request->validate(['valor_simulacao' => 'required|numeric'])['valor_simulacao'];
        }

        $processo->update($updateData);

        return response()->json([
            'message' => 'Etapa avançada com sucesso!',
            'etapa' => $novaEtapa,
            'descricao' => Atendimento::$etapas[$novaEtapa] ?? null,
        ]);
    }

    /**
     * 🔙 Retorna o processo para a etapa anterior
     */
    public function voltarEtapa($id)
    {
        $user = auth()->user();
        $processo = Atendimento::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $etapaAnterior = $processo->etapa;

        $novaEtapa = $processo->getEtapaAnterior();

        if ($novaEtapa === $etapaAnterior) {
            return response()->json(['message' => 'O processo já está na primeira etapa.']);
        }

        $processo->update(['etapa' => $novaEtapa]);

        return response()->json([
            'message' => 'Etapa retornada com sucesso!',
            'etapa' => $novaEtapa,
            'descricao' => Atendimento::$etapas[$novaEtapa] ?? null,
        ]);
    }

    public function adicionarImovel(Request $request, $id)
    {
        $user = auth()->user();
        $processo = Atendimento::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate(['imovel_id' => 'required|integer']);
        $processo->imovel_id = $request->imovel_id;
        $processo->save();

        $imovel = Imovel::find($request->imovel_id);
        $imovel->disponivel = false;
        $imovel->save();

        return response()->json(['message' => 'Imóvel adicionado ao processo com sucesso']);
    }

    public function groupedByEtapa(Request $request)
    {
        $user = auth()->user();
        $query = Atendimento::with(['cliente', 'corretor']);

        if ($user->role === 'CORRETOR') {
            $query->where('corretor_id', $user->id);
        } elseif ($user->role === 'CLIENTE') {
            $query->where('cliente_id', $user->id);
        }

        $processos = $query->get();
        $grouped = $processos->groupBy(function ($processo) {
            return Atendimento::$etapas[$processo->etapa] ?? $processo->etapa;
        });
        return response()->json($grouped);
    }
}
