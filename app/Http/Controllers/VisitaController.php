<?php
namespace App\Http\Controllers;

use App\Models\Visita;
use Illuminate\Http\Request;

class VisitaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Visita::with(['imovel', 'processo']);

        if ($user->role === 'CORRETOR' || $user->role === 'CLIENTE') {
            $query->whereHas('processo', function ($q) use ($user) {
                if ($user->role === 'CORRETOR') {
                    $q->where('corretor_id', $user->id);
                } elseif ($user->role === 'CLIENTE') {
                    $q->where('cliente_id', $user->id);
                }
            });
        }


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
            'processo_id' => 'nullable|integer',
            'imovel_id' => 'nullable|integer'
        ]);

        $visita = Visita::create($data);
        return response()->json($visita, 201);
    }

    public function show($id)
    {
        $user = auth()->user();
        $visita = Visita::with(['imovel','processo'])->findOrFail($id);

        if ($user->role === 'CORRETOR' && $visita->processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $visita->processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($visita);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $visita = Visita::findOrFail($id);

        if ($user->role === 'CORRETOR' && $visita->processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $visita->processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $visita->update($request->all());
        return response()->json($visita);
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $visita = Visita::findOrFail($id);

        if ($user->role === 'CORRETOR' && $visita->processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $visita->processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Visita::destroy($id);
        return response()->json(null, 204);
    }

    public function confirmar($id)
    {
        $user = auth()->user();
        $visita = Visita::findOrFail($id);

        if ($user->role === 'CORRETOR' && $visita->processo->corretor_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($user->role === 'CLIENTE' && $visita->processo->cliente_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $visita->update(['confirmada' => true]);
        return response()->json(['message' => 'Visita confirmada com sucesso']);
    }
}
