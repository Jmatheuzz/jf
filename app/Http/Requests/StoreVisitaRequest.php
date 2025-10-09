<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisitaRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'data_visita' => 'nullable|date',
            'imovel_id' => 'nullable|integer',
            'processo_id' => 'nullable|integer'
        ];
    }
}
