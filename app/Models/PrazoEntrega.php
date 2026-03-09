<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrazoEntrega extends Model
{
    use HasFactory;

    protected $table = 'prazos_entrega';

    protected $fillable = [
        'nome',
        'icone',
        'dias',
        'prioridade',
        'permite_mapa',
        'ativo'
    ];

    protected $casts = [
        'dias' => 'integer',
        'prioridade' => 'integer',
        'permite_mapa' => 'boolean',
        'ativo' => 'boolean'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'prazo_id');
    }
}
