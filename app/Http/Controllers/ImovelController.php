<?php

namespace App\Http\Controllers;

use App\Models\Imovel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImovelController extends Controller
{
    public function index(Request $request)
    {
        $query = Imovel::query()->with('fotos');

        if ($request->filled('cidade')) {
            $query->where('cidade', 'like', "%{$request->cidade}%");
        }

        if ($request->filled('endereco')) {
            $query->where('endereco', 'like', "%{$request->endereco}%");
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('nome')) {
            $query->where('nome_arquivo', 'like', "%{$request->nome}%");
        }

        return response()->json($query->get());
    }

    public function indexPublic(Request $request)
    {
        $query = Imovel::query()->with('fotos');

        if ($request->filled('cidade')) {
            $query->where('cidade', 'like', "%{$request->cidade}%");
        }

        if ($request->filled('endereco')) {
            $query->where('endereco', 'like', "%{$request->endereco}%");
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('nome')) {
            $query->where('nome_arquivo', 'like', "%{$request->nome}%");
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cidade' => 'nullable|string',
            'endereco' => 'nullable|string',
            'tipo' => 'nullable|string',
            'valor' => 'nullable|numeric',
            'numero_banheiros' => 'nullable|numeric',
            'numero_quartos' => 'nullable|numeric',
            'area' => 'nullable|numeric',
            'descricao' => 'nullable|string',
        ]);

        $imovel = Imovel::create($data);

        return response()->json($imovel, 201);
    }

    public function show($id)
    {
        $imovel = Imovel::with(['fotos'])->findOrFail($id);

        return response()->json($imovel);
    }

    public function update(Request $request, $id)
    {
        $imovel = Imovel::findOrFail($id);
        $imovel->update($request->all());

        return response()->json($imovel);
    }

    /**
     * Apaga um imóvel e todas as suas fotos associadas (banco + arquivos físicos)
     */
    public function destroy($id)
    {
        $imovel = Imovel::with('fotos')->findOrFail($id);

        // Deletar arquivos físicos das fotos
        foreach ($imovel->fotos as $foto) {
            if ($foto->caminho && Storage::disk('public')->exists($foto->caminho)) {
                Storage::disk('public')->delete($foto->caminho);
            }
        }

        // Deletar registros de fotos do banco
        $imovel->fotos()->delete();

        // Deletar o imóvel em si
        $imovel->delete();

        return response()->json([
            'message' => 'Imóvel e todas as fotos associadas foram removidos com sucesso.'
        ]);
    }
}
