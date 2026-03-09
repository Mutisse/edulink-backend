<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory;

    protected $table = 'transacoes';

    protected $fillable = [
        'explicador_id',
        'pedido_id',
        'conteudo_id',
        'descricao',
        'valor',
        'comissao',
        'tipo',
        'carteira',
        'referencia',
        'status'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'comissao' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o explicador (User)
     */
    public function explicador()
    {
        return $this->belongsTo(User::class, 'explicador_id');
    }

    /**
     * Relacionamento com o pedido
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Relacionamento com o conteúdo
     */
    public function conteudo()
    {
        return $this->belongsTo(Conteudo::class);
    }

    /**
     * Escopo para entradas (receitas)
     */
    public function scopeEntradas($query)
    {
        return $query->where('tipo', 'entrada');
    }

    /**
     * Escopo para saídas (saques)
     */
    public function scopeSaidas($query)
    {
        return $query->where('tipo', 'saida');
    }

    /**
     * Escopo para transações de um explicador
     */
    public function scopeDoExplicador($query, $explicadorId)
    {
        return $query->where('explicador_id', $explicadorId);
    }

    /**
     * Escopo para transações de hoje
     */
    public function scopeHoje($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Escopo para transações desta semana
     */
    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Escopo para transações deste mês
     */
    public function scopeEsteMes($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }

    /**
     * Calcular saldo de um explicador
     */
    public static function saldoDoExplicador($explicadorId)
    {
        $entradas = self::doExplicador($explicadorId)
            ->entradas()
            ->sum('valor');

        $saidas = self::doExplicador($explicadorId)
            ->saidas()
            ->sum('valor');

        return $entradas - $saidas;
    }

    /**
     * Verificar se é uma entrada
     */
    public function isEntrada(): bool
    {
        return $this->tipo === 'entrada';
    }

    /**
     * Verificar se é uma saída
     */
    public function isSaida(): bool
    {
        return $this->tipo === 'saida';
    }

    /**
     * Formatar valor com símbolo MZN
     */
    public function valorFormatado(): string
    {
        return number_format($this->valor, 0, ',', '.') . ' MZN';
    }

    /**
     * Pegar ícone baseado no tipo
     */
    public function getIconeAttribute(): string
    {
        return $this->tipo === 'entrada' ? 'arrow_upward' : 'arrow_downward';
    }

    /**
     * Pegar cor baseada no tipo
     */
    public function getCorAttribute(): string
    {
        return $this->tipo === 'entrada' ? 'positive' : 'negative';
    }
}
