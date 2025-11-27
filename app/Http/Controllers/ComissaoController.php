<?php

namespace App\Http\Controllers;

use App\Models\Comissao;
use Illuminate\Http\Request;

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
}
