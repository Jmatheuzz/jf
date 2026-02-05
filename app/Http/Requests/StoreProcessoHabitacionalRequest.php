<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProcessoHabitacionalRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'cliente_id' => 'required|integer',
            'corretor_id' => 'nullable|integer',
            'imovel_id' => 'nullable|integer',
            'correspondenteBancario' => 'nullable|string',
            'nomeConstrutora' => 'nullable|string',
        ];
    }
}
