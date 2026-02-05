<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProcessoHabitacional extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'processos_habitacionais';
    protected $fillable = ['cliente_id','corretor_id','imovel_id','etapa','interesse', 'observacao', 'status_etapa', 'correspondenteBancario', 'data_assinatura_empreitada', 'nomeConstrutora'];
    protected $with = ['cliente', 'corretor', 'imovel'];
    protected $appends = ['descricao_etapa'];

    public function cliente() { return $this->belongsTo(User::class); }
    public function corretor() { return $this->belongsTo(User::class); }
    public function imovel() { return $this->belongsTo(Imovel::class); }
    public function historico() { return $this->hasMany(ProcessoHabitacionalHistory::class, 'processo_id'); }
    public function documentos() { return $this->hasMany(DocumentoProcessoHabitacional::class); }

    protected static function booted()
    {
        static::deleting(function ($processo) {
            foreach ($processo->documentos as $documento) {
                Storage::delete($documento->path);
            }
        });
    }

    public const STATUS_PENDENTE = 'PENDENTE';
    public const STATUS_CONCLUIDA = 'CONCLUIDA';

    public static $etapas = [
        'CONTRATO_EMPREITADA'   => 'Contrato de Empreitada',
        'CONFECCAO_PROJETO'     => 'Confecção do Projeto',
        'APROVACAO_MUNICIPAL'    => 'Aprovação municipal',
        'ABERTURA_OS' => 'Abertura de Ordem de serviço',
        'AVALIACAO_CAIXA' => 'Avaliação da engenharia caixa',
        'CONFORMIDADE_PROCESSO' => 'Conformidade do processo',
        'ASSINATURA_CONTRATO'   => 'Assinatura do Contrato',
        'REGISTRO_CARTORIO'     => 'Registro em Cartório',
        'HABITISE_EMITIDO'     => 'Habite-se emitido',
        'AVERBACAO_OBRA'     => 'Averbação da obra',
        'IMOVEL_ENTREGUE'            => 'Imóvel entregue',
    ];

    // 🔙 Etapa anterior
    public function getEtapaAnterior(): string
    {
        $etapas = array_keys(self::$etapas);
        $indiceAtual = array_search($this->etapa, $etapas);

        if ($indiceAtual !== false && $indiceAtual > 0) {
            return $etapas[$indiceAtual - 1];
        }

        // se já for a primeira, retorna a atual
        return $this->etapa;
    }

    // 🔜 Próxima etapa
    public function getProximaEtapa(): string
    {
        $etapas = array_keys(self::$etapas);
        $indiceAtual = array_search($this->etapa, $etapas);

        if ($indiceAtual !== false && $indiceAtual < count($etapas) - 1) {
            return $etapas[$indiceAtual + 1];
        }

        // se for a última, retorna a atual
        return $this->etapa;
    }

    // 🔼 Avança o processo e salva no banco
    public function avancarEtapa(): void
    {
        if ($this->status_etapa === self::STATUS_PENDENTE) {
            $this->update(['status_etapa' => self::STATUS_CONCLUIDA]);
        } else {
            $novaEtapa = $this->getProximaEtapa();
            if ($novaEtapa !== $this->etapa) {
                $this->update([
                    'etapa' => $novaEtapa,
                    'status_etapa' => self::STATUS_PENDENTE
                ]);
            }
        }
    }

    // 🔽 Retrocede o processo e salva no banco
    public function retrocederEtapa(): void
    {
        $novaEtapa = $this->getEtapaAnterior();
        if ($novaEtapa !== $this->etapa) {
            $this->update([
                'etapa' => $novaEtapa,
                'status_etapa' => self::STATUS_PENDENTE
            ]);
        }
    }

    public function getDescricaoEtapaAttribute(): string
    {
        return self::$etapas[$this->etapa] ? self::$etapas[$this->etapa] . ' (' . $this->status_etapa . ')' : 'Desconhecida';
    }

    // 🧠 Retorna o nome descritivo da etapa atual
    public function getEtapaDescricao(): string
    {
        return self::$etapas[$this->etapa] ? self::$etapas[$this->etapa] . ' (' . $this->status_etapa . ')' : 'Desconhecida';
    }
}