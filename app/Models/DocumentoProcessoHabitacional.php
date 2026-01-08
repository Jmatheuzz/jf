<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentoProcessoHabitacional extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'processo_habitacional_id',
        'user_id',
        'path',
        'nome_original',
    ];

    public function processoHabitacional()
    {
        return $this->belongsTo(ProcessoHabitacional::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
