<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'email',
        'password',
        'tipo',
        'avatar',
        'telefone',
        'status',
        'ultimo_acesso'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'ultimo_acesso' => 'datetime',
    ];

    const TIPOS = ['estudante', 'explicador', 'admin'];
    const STATUS = ['Ativo', 'Inativo', 'Bloqueado', 'Pendente'];

    public function estudante()
    {
        return $this->hasOne(Estudante::class);
    }

    public function explicador()
    {
        return $this->hasOne(Explicador::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'estudante_id');
    }

    public function propostas()
    {
        return $this->hasMany(Proposta::class, 'explicador_id');
    }

    public function conteudos()
    {
        return $this->hasMany(Conteudo::class, 'explicador_id');
    }

    public function biblioteca()
    {
        return $this->hasMany(Biblioteca::class, 'estudante_id');
    }

    public function carrinho()
    {
        return $this->hasMany(Carrinho::class, 'estudante_id');
    }
}
