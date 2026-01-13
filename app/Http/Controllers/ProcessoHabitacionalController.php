<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProcessoHabitacionalRequest;
use App\Models\Comissao;
use App\Models\Imovel;
use App\Models\ProcessoHabitacional;
use App\Models\ProcessoHabitacionalHistory;
use App\Services\TimelineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcessoHabitacionalController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ProcessoHabitacional::with(['cliente', 'imovel', 'corretor']);

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

        if ($request->filled('etapa')) {
            $query->where('etapa', array_search($request->etapa, ProcessoHabitacional::$etapas));
        }

        return response()->json($query->get());
    }

    public function store(StoreProcessoHabitacionalRequest $request)
    {
        $data = $request->validated();
        $data['etapa'] = array_key_first(ProcessoHabitacional::$etapas);
        $processo = ProcessoHabitacional::create($data);
        return response()->json($processo, 201);
    }

    public function show($id)
    {
        $user = auth()->user();
        $processo = ProcessoHabitacional::with(['cliente', 'imovel', 'corretor', 'documentos'])->findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $documentos = $processo->documentos->map(function ($documento) {
            $documento->url = Storage::url($documento->path);
            return $documento;
        });

        $timeline = TimelineService::montarTimeline($processo->etapa, $processo->status_etapa);

        return response()->json([
            'processo' => $processo,
            'documentos' => $documentos,
            'timeline' => $timeline
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $processo = ProcessoHabitacional::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $processo->update($request->all());
        return response()->json($processo);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $processo = ProcessoHabitacional::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        ProcessoHabitacional::destroy($id);
        return response()->json(null, 204);
    }

    public function avancarEtapa($id)
    {
        $user = auth()->user();
        $processo = ProcessoHabitacional::with(['imovel'])->findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $processo->avancarEtapa();
        

        if ($processo->etapa === 'ASSINATURA_CONTRATO' && $processo->status_etapa === 'CONCLUIDA') {
            Comissao::create([
                'processo_habitacional_id' => $processo->id,
                'valor' => ($processo->imovel->valor - 10000) * 0.03,
                'pago' => false,
            ]);
        }
        $processo->save();
        ProcessoHabitacionalHistory::create([
            'processo_id' => $processo->id,
            'etapa'       => $processo->etapa,
            'observacao'  => "Avançou para {$processo->etapa} via API"
        ]);
    

        return response()->json([
            'message' => 'Etapa avançada com sucesso!',
            'etapa' => $processo->etapa,
            'status_etapa' => $processo->status_etapa,
            'descricao' => $processo->getEtapaDescricao(),
        ]);
    }

    /**
     * 🔙 Retorna o processo para a etapa anterior
     */
    public function voltarEtapa($id)
    {
        $user = auth()->user();
        $processo = ProcessoHabitacional::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $etapaAnterior = $processo->etapa;

        $novaEtapa = $processo->getEtapaAnterior();

        if ($novaEtapa === $etapaAnterior) {
            return response()->json(['message' => 'O processo já está na primeira etapa.'], 400);
        }

        $processo->retrocederEtapa();

        ProcessoHabitacionalHistory::create([
            'processo_id' => $processo->id,
            'etapa'       => $novaEtapa,
            'observacao'  => "Retrocedeu da etapa {$etapaAnterior} para {$novaEtapa} via API"
        ]);

        return response()->json([
            'message' => 'Etapa retornada com sucesso!',
            'etapa' => $processo->etapa,
            'status_etapa' => $processo->status_etapa,
            'descricao' => $processo->getEtapaDescricao(),
        ]);
    }

    public function adicionarImovel(Request $request, $id)
    {
        $user = auth()->user();
        $processo = ProcessoHabitacional::findOrFail($id);

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

    public function groupedByEtapa()
    {
        $user = auth()->user();
        $query = ProcessoHabitacional::with(['cliente', 'imovel', 'corretor']);

        if ($user->role === 'CORRETOR') {
            $query->where('corretor_id', $user->id);
        } elseif ($user->role === 'CLIENTE') {
            $query->where('cliente_id', $user->id);
        }

        $processos = $query->get();
        $grouped = $processos->groupBy(function ($processo) {
            return ProcessoHabitacional::$etapas[$processo->etapa] ?? $processo->etapa;
        });
        return response()->json($grouped);
    }

    public function avancarEtapaEsteira($id)
    {
        $user = auth()->user();
        $processo = ProcessoHabitacional::findOrFail($id);

        if ($user->role === 'CORRETOR' && $processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $etapaAnterior = $processo->etapa;
        $novaEtapa = $processo->getProximaEtapa();

        if ($novaEtapa !== $etapaAnterior) {
            $processo->update([
                'etapa' => $novaEtapa,
                'status_etapa' => ProcessoHabitacional::STATUS_PENDENTE
            ]);
        } else {
            return response()->json(['message' => 'O processo já está na última etapa.'], 400);
        }

        ProcessoHabitacionalHistory::create([
            'processo_id' => $processo->id,
            'etapa'       => $processo->etapa,
            'observacao'  => "Avançou da etapa {$etapaAnterior} para {$processo->etapa} (esteira) via API"
        ]);

        return response()->json([
            'message' => 'Etapa avançada com sucesso!',
            'etapa' => $processo->etapa,
            'status_etapa' => $processo->status_etapa,
            'descricao' => $processo->getEtapaDescricao(),
        ]);
    }
}
