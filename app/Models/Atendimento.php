<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atendimento extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'atendimentos';
    protected $fillable = ['cliente_id','corretor_id','etapa','interesse', 'observacao', 'is_active', 'motivoCancelamento'];
    protected $appends = ['descricao_etapa'];

    public function cliente() { return $this->belongsTo(User::class); }
    public function corretor() { return $this->belongsTo(User::class); }

    public static $etapas = [
        'SIMULACAO'   => 'Simulação',
        'COLHER_DOCUMENTACAO'       => 'Colher documentação',
        'ABERTURA_CONTA'              => 'Abertura de conta',
        'CONFORMIDADE_CONTA'  => 'Conformidade de conta',
        'ANALISE_CREDITO'   => 'Análise de crédito',
        'CLIENTE_APROVADO'   => 'Cliente aprovado',
        'AGUARDANDO_ENTREVISTA'   => 'Aguardando entrevista',
        'ENTREVISTA_APROVADA'   => 'Entrevista aprovada',
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
        $novaEtapa = $this->getProximaEtapa();
        if ($novaEtapa !== $this->etapa) {
            $this->update(['etapa' => $novaEtapa]);
        }
    }

    // 🔽 Retrocede o processo e salva no banco
    public function retrocederEtapa(): void
    {
        $novaEtapa = $this->getEtapaAnterior();
        if ($novaEtapa !== $this->etapa) {
            $this->update(['etapa' => $novaEtapa]);
        }
    }

    // 🧠 Retorna o nome descritivo da etapa atual
    public function getEtapaDescricao(): string
    {
        return self::$etapas[$this->etapa] ?? 'Desconhecida';
    }

    public function getDescricaoEtapaAttribute(): string
    {
        return self::$etapas[$this->etapa] ?? 'Desconhecida';
    }
}