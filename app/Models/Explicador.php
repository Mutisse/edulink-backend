<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Explicador extends Model
{
    use HasFactory;

    protected $table = 'explicadores';

    protected $fillable = [
        'user_id',
        'descricao',
        'materias',
        'avaliacao',
        'total_avaliacoes',
        'sessoes_realizadas',
        'estudantes_atendidos',
        'taxa_aceitacao',
        'preco_medio',
        'disponibilidade',
        'status'
    ];

    protected $casts = [
        'materias' => 'array',
        'avaliacao' => 'float',
        'disponibilidade' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conteudos()
    {
        return $this->hasMany(Conteudo::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function transacoes()
    {
        return $this->hasMany(Transacao::class);
    }
}
