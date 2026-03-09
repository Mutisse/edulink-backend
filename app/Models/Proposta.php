<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposta extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'explicador_id',
        'valor',
        'mensagem',
        'status'
    ];

    const STATUS = ['pendente', 'aceita', 'recusada'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function explicador()
    {
        return $this->belongsTo(User::class, 'explicador_id');
    }
}
