<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosVenda extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['canal','data_contato','descricao','resolvido','cliente_id'];
    public function cliente() { return $this->belongsTo(Cliente::class); }
}