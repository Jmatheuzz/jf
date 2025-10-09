<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    use HasFactory;
    protected $fillable = ['data_visita','visitado','imovel_id','processo_id','confirmada'];
    public function imovel() { return $this->belongsTo(Imovel::class); }
    public function processo() { return $this->belongsTo(ProcessoHabitacional::class); }
}