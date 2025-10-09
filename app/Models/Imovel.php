<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imovel extends Model

{
    use HasFactory;

    protected $table = 'imoveis';
    protected $fillable = ['cidade','endereco','tipo','valor','descricao','area','numero_banheiros','numero_quartos','disponivel'];
    public function fotos() { return $this->hasMany(FotoImovel::class); }
    public function processos() { return $this->hasMany(ProcessoHabitacional::class); }
}