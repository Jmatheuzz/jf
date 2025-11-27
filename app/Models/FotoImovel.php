<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class FotoImovel extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'fotos_imovel';
    protected $fillable = ['nome_arquivo','ordem','imovel_id', 'caminho'];
    protected $appends = ['url']; 
    public function imovel() { return $this->belongsTo(Imovel::class); }

    public function getUrlAttribute(): string
    {
        // Usa o disco 'public' (que você usou em store()) e o caminho salvo no DB
        return Storage::url($this->caminho); 
    }
}