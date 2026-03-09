<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudante extends Model
{
    use HasFactory;

    protected $table = 'estudantes';

    protected $fillable = [
        'user_id',
        'nivel',
        'instituicao',
        'pedidos_realizados',
        'sessoes_realizadas',
        'saldo'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function biblioteca()
    {
        return $this->hasMany(Biblioteca::class);
    }

    public function carrinho()
    {
        return $this->hasMany(Carrinho::class);
    }
}
