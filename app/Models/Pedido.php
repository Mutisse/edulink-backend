<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudante_id',
        'explicador_id',
        'materia',
        'titulo',
        'descricao',
        'tipo_servico',
        'nivel',
        'duracao',
        'prazo',
        'preco',
        'preco_combinado',
        'local',
        'urgente',
        'status',
        'visualizacoes',
        'data_sessao',
        'plataforma'
    ];

    protected $casts = [
        'urgente' => 'boolean',
        'data_sessao' => 'datetime'
    ];

    const STATUS = [
        'Aguardando',
        'Em negociação',
        'Aceito',
        'Em andamento',
        'Concluído',
        'Cancelado'
    ];

    public function estudante()
    {
        return $this->belongsTo(User::class, 'estudante_id');
    }

    public function explicador()
    {
        return $this->belongsTo(User::class, 'explicador_id');
    }

    public function propostas()
    {
        return $this->hasMany(Proposta::class);
    }
}
