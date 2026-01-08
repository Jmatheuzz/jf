<?php

namespace App\Http\Controllers;

use App\Models\DocumentoProcessoHabitacional;
use App\Models\ProcessoHabitacional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoProcessoHabitacionalController extends Controller
{

    public function store(Request $request, $processo_id)
    {
        $request->validate([
            'documentos' => 'required|array',
            'documentos.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $processo = ProcessoHabitacional::findOrFail($processo_id);
        $documentosCriados = [];

        foreach ($request->file('documentos') as $file) {
            $originalName = $file->getClientOriginalName();
            $path = $file->store('documentos_processo_habitacional', 'public');

            $documento = $processo->documentos()->create([
                'user_id' => auth()->id(),
                'path' => $path,
                'nome_original' => $originalName,
            ]);

            $documentosCriados[] = $documento;
        }

        return response()->json(['msg' => 'Documentos enviados com sucesso.']);
    }

    public function destroy($id)
    {
        $documento = DocumentoProcessoHabitacional::findOrFail($id);

        // TODO: Adicionar verificação de permissão do usuário

        Storage::disk('public')->delete($documento->path);
        $documento->delete();

        return response()->json(['msg' => 'Documentos excluídos com sucesso.']);
    }
}