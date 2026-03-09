<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelEnsino extends Model
{
    use HasFactory;

    protected $table = 'niveis_ensino';

    protected $fillable = [
        'nome',
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
        return $this->hasMany(Pedido::class, 'nivel_id');
    }

    public function instituicoes()
    {
        return $this->hasMany(InstituicaoEnsino::class, 'nivel_ensino_id');
    }
}
