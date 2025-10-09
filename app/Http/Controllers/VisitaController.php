<?php
namespace App\Http\Controllers;

use App\Models\Visita;
use Illuminate\Http\Request;

class VisitaController extends Controller
{
    public function index(Request $request)
    {
        $query = Visita::with(['imovel', 'processo']);

        if ($request->filled('imovel_id')) {
            $query->where('imovel_id', $request->imovel_id);
        }

        if ($request->filled('processo_id')) {
            $query->where('processo_id', $request->processo_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'data_visita' => 'nullable|date',
            'imovel_id' => 'nullable|integer',
            'processo_id' => 'nullable|integer',
            'imovel_id' => 'nullable|integer'
        ]);

        $visita = Visita::create($data);
        return response()->json($visita, 201);
    }

    public function show($id)
    {
        return response()->json(Visita::with(['imovel','processo'])->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $visita = Visita::findOrFail($id);
        $visita->update($request->all());
        return response()->json($visita);
    }

    public function destroy($id)
    {
        Visita::destroy($id);
        return response()->json(null, 204);
    }

    public function confirmar($id)
    {
        $visita = Visita::findOrFail($id);
        $visita->update(['confirmada' => true]);
        return response()->json(['message' => 'Visita confirmada com sucesso']);
    }
}
