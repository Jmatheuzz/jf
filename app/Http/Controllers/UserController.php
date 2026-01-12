<?php
namespace App\Http\Controllers;

use App\Models\ProcessoHabitacional;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
{
    $query = User::query();

    if ($request->filled('name')) {
        $query->where('name', 'like', "%{$request->name}%");
    }

    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    return response()->json($query->get());
}

    public function getCorretores(Request $request)
    {
        $query = User::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        return response()->json($query->where('role', '=', 'CORRETOR')->get());
    }

    public function getClientes(Request $request)
    {
        $query = User::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        return response()->json($query->where('role', '=', 'CLIENTE')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string',
            'role' => 'required|string',
            'telefone' => 'required|string',
            'creci' => 'nullable',
            'rg' => 'nullable',
            'cpf' => 'nullable',
            'estado_civil' => 'nullable',
            'renda' => 'nullable',
            'profissao' => 'nullable',
            'possui_fgts' => 'nullable',
            'possui_filhos_menor' => 'nullable'
        ]);

        $data['password'] = bcrypt($data['password']);
        $data['email_verified_at'] = now();

        $user = User::create($data);
        return response()->json($user, 201);
    }

    public function show($id)
    {
        return response()->json(User::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->except(['password']));
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
            $user->save();
        }
        return response()->json($user);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(null, 204);
    }

    public function getProcessosByCorretor(User $user)
    {
        $authenticatedUser = Auth::user();

        if ($authenticatedUser->role !== 'ADMIN' && ($authenticatedUser->role !== 'CORRETOR' || $authenticatedUser->id !== $user->id)) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }
        
        if ($user->role !== 'CORRETOR') {
            return response()->json(['error' => 'O usuário especificado não é um corretor.'], 400);
        }

        $processos = ProcessoHabitacional::where('corretor_id', $user->id)->get();

        $formattedProcessos = $processos->map(function ($processo) {
            return [
                'corretor_nome' => $processo->corretor->name,
                'cliente_nome' => $processo->cliente->name,
                'etapa' => $processo->descricao_etapa,
                'status_etapa' => $processo->status_etapa,
                'imovel_nome' => $processo->imovel ? $processo->imovel->endereco . ' - ' . $processo->imovel->cidade : null,
                'imovel_valor' => $processo->imovel ? $processo->imovel->valor : null,
                'observacoes' => $processo->observacao,
            ];
        });

        return response()->json($formattedProcessos);
    }
}
