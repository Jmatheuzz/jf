<?php
namespace App\Http\Controllers;

use App\Models\ProcessoHabitacionalHistory;
use Illuminate\Http\Request;

class ProcessoHabitacionalHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcessoHabitacionalHistory::query();

        if ($request->filled('processo_id')) {
            $query->where('processo_id', $request->processo_id);
        }

        $perPage = $request->get('per_page', 15);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'processo_id' => 'required|integer',
            'etapa' => 'required|integer',
            'observacao' => 'nullable|string'
        ]);
        $h = ProcessoHabitacionalHistory::create($data);
        return response()->json($h, 201);
    }

    public function show($id)
    {
        return response()->json(ProcessoHabitacionalHistory::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $h = ProcessoHabitacionalHistory::findOrFail($id);
        $h->update($request->all());
        return response()->json($h);
    }

    public function destroy($id)
    {
        ProcessoHabitacionalHistory::destroy($id);
        return response()->json(null, 204);
    }
}
