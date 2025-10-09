<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreImovelRequest extends FormRequest
{
    public function authorize() { return true; }
    public function rules()
    {
        return [
            'cidade' => 'nullable|string',
            'endereco' => 'nullable|string',
            'tipo' => 'nullable|string',
            'valor' => 'nullable|numeric',
        ];
    }
}
