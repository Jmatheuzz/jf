<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessoHabitacional extends Model
{
    use HasFactory;
    protected $table = 'processos_habitacionais';
    protected $fillable = ['cliente_id','corretor_id','imovel_id','etapa','interesse'];

    public function cliente() { return $this->belongsTo(User::class); }
    public function corretor() { return $this->belongsTo(User::class); }
    public function imovel() { return $this->belongsTo(Imovel::class); }
    public function historico() { return $this->hasMany(ProcessoHabitacionalHistory::class, 'processo_id'); }

    private static $etapas = [
        'COLETA_DOCUMENTACAO'   => 'Coleta de Documentação',
        'ANALISE_CREDITO'       => 'Análise de Crédito',
        'RESERVA'               => 'Reserva do Imóvel',
        'CONTRATO_EMPREITADA'   => 'Contrato de Empreitada',
        'CONFECCAO_PROJETO'     => 'Confecção do Projeto',
        'ENTREGA_PREFEITURA'    => 'Entrega na Prefeitura',
        'ANALISE_CREDITO_CAIXA' => 'Análise de Crédito Caixa',
        'AVALIACAO_IMOVEL_CAIXA'=> 'Avaliação do Imóvel Caixa',
        'ASSINATURA_CONTRATO'   => 'Assinatura do Contrato',
        'REGISTRO_CARTORIO'     => 'Registro em Cartório',
        'FINALIZADO'            => 'Processo Finalizado',
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
}