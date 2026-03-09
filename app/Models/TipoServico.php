<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoServico extends Model
{
    use HasFactory;

    protected $table = 'tipos_servico';

    protected $fillable = [
        'nome',
        'icone',
        'descricao',
        'ativo',
        'ordem'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'tipo_servico_id');
    }
}
