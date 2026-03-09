<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avaliacao extends Model
{
    use HasFactory;

    protected $table = 'avaliacoes';

    protected $fillable = [
        'pedido_id',
        'estudante_id',
        'explicador_id',
        'nota',
        'comentario',
        'resposta'
    ];

    protected $casts = [
        'nota' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relacionamento com o pedido
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    /**
     * Relacionamento com o estudante que avaliou
     */
    public function estudante()
    {
        return $this->belongsTo(User::class, 'estudante_id');
    }

    /**
     * Relacionamento com o explicador avaliado
     */
    public function explicador()
    {
        return $this->belongsTo(User::class, 'explicador_id');
    }

    /**
     * Escopo para avaliações de um explicador
     */
    public function scopeDoExplicador($query, $explicadorId)
    {
        return $query->where('explicador_id', $explicadorId);
    }

    /**
     * Escopo para avaliações de um estudante
     */
    public function scopeDoEstudante($query, $estudanteId)
    {
        return $query->where('estudante_id', $estudanteId);
    }

    /**
     * Escopo para avaliações de um pedido
     */
    public function scopeDoPedido($query, $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId);
    }

    /**
     * Escopo para avaliações com nota mínima
     */
    public function scopeNotaMinima($query, $nota)
    {
        return $query->where('nota', '>=', $nota);
    }

    /**
     * Verificar se a avaliação tem resposta
     */
    public function getTemRespostaAttribute()
    {
        return !is_null($this->resposta);
    }

    /**
     * Formatar data de criação
     */
    public function getDataFormatadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Pegar estrelas em texto
     */
    public function getEstrelasAttribute()
    {
        return str_repeat('★', $this->nota) . str_repeat('☆', 5 - $this->nota);
    }
}
