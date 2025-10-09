<?php
namespace App\Http\Controllers;

use App\Models\FotoImovel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoImovelController extends Controller
{
    public function index(Request $request)
    {
        $query = FotoImovel::query();

        if ($request->filled('imovel_id')) {
            $query->where('imovel_id', $request->imovel_id);
        }

        $perPage = $request->get('per_page', 15);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $request->validate([
            'imovel_id' => 'required|exists:imoveis,id',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'descricao' => 'nullable|string|max:255',
        ]);

        $path = $request->file('foto')->store('fotos_imoveis', 'public');

        $foto = FotoImovel::create([
            'imovel_id' => $request->imovel_id,
            'caminho' => $path,
            'descricao' => $request->descricao,
        ]);

        return response()->json([
            'message' => 'Foto adicionada com sucesso.',
            'data' => $foto
        ], 201);
    }

    public function storeMultiple(Request $request)
    {
        $request->validate([
            'imovel_id' => 'required|exists:imoveis,id',
            'fotos.*' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'descricoes' => 'nullable|array',
        ]);

        $fotosCriadas = [];

        foreach ($request->file('fotos') as $index => $fotoFile) {
            $path = $fotoFile->store('fotos_imoveis', 'public');

            $descricao = $request->descricoes[$index] ?? null;

            $foto = FotoImovel::create([
                'imovel_id' => $request->imovel_id,
                'caminho' => $path,
                'descricao' => $descricao,
            ]);

            $fotosCriadas[] = $foto;
        }

        return response()->json([
            'message' => 'Fotos adicionadas com sucesso.',
            'data' => $fotosCriadas
        ], 201);
    }

    public function show($id)
    {
        return response()->json(FotoImovel::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $foto = FotoImovel::findOrFail($id);
        $foto->update($request->only(['descricao']));
        return response()->json($foto);
    }

    /**
     * Deleta uma foto pelo caminho/URL
     */
    public function destroy () {
        return response()->json(['message' => 'Use o endpoint destroyByPath para deletar por caminho/URL.'], 400);
    }
    public function destroyByPath(Request $request)
    {
        $request->validate([
            'url' => 'required|string'
        ]);

        // Extrai o caminho relativo a partir da URL pública gerada por Storage::url()
        $publicPathPrefix = Storage::url('');
        $url = $request->url;

        if (!str_starts_with($url, $publicPathPrefix)) {
            return response()->json(['error' => 'URL inválida ou não pertence ao armazenamento público.'], 400);
        }

        // Exemplo: /storage/fotos_imoveis/arquivo.jpg → fotos_imoveis/arquivo.jpg
        $relativePath = str_replace($publicPathPrefix, '', $url);

        // Tenta encontrar no banco
        $foto = FotoImovel::where('caminho', $relativePath)->first();

        if (!$foto) {
            return response()->json(['error' => 'Foto não encontrada no banco de dados.'], 404);
        }

        // Deleta o arquivo físico se existir
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        // Deleta o registro
        $foto->delete();

        return response()->json([
            'message' => 'Foto removida com sucesso.'
        ]);
    }
}
