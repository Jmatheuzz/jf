<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosVenda extends Model
{
    use HasFactory;
    protected $fillable = ['canal','data_contato','descricao','resolvido','cliente_id'];
    public function cliente() { return $this->belongsTo(Cliente::class); }
}