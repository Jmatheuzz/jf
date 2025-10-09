<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePosVendaRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'canal' => 'nullable|string',
            'data_contato' => 'nullable|date',
            'descricao' => 'nullable|string'
        ];
    }
}
