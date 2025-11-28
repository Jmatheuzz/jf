<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comissao extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'comissoes';

    protected $fillable = [
        'processo_habitacional_id',
        'valor',
        'pago',
    ];

    protected $with = ['processoHabitacional'];

    public function processoHabitacional(): BelongsTo
    {
        return $this->belongsTo(ProcessoHabitacional::class);
    }
}
