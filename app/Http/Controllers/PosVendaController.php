<?php
namespace App\Http\Controllers;

use App\Models\PosVenda;
use Illuminate\Http\Request;

class PosVendaController extends Controller
{
    public function index(Request $request)
    {
        $query = PosVenda::with('cliente');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        $perPage = $request->get('per_page', 15);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'canal' => 'nullable|string',
            'data_contato' => 'nullable|date',
            'descricao' => 'nullable|string',
            'cliente_id' => 'nullable|integer'
        ]);
        $p = PosVenda::create($data);
        return response()->json($p, 201);
    }

    public function show($id)
    {
        return response()->json(PosVenda::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $p = PosVenda::findOrFail($id);
        $p->update($request->all());
        return response()->json($p);
    }

    public function destroy($id)
    {
        PosVenda::destroy($id);
        return response()->json(null, 204);
    }
}
