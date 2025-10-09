<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessoHabitacionalHistory extends Model
{
    use HasFactory;
    protected $table = 'processos_habitacional_history';
    protected $fillable = ['processo_id','etapa','observacao'];
    public function processo() { return $this->belongsTo(ProcessoHabitacional::class,'processo_id'); }
}