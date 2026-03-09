<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conteudo extends Model
{
    use HasFactory;

    protected $fillable = [
        'explicador_id',
        'titulo',
        'materia',
        'nivel',
        'descricao',
        'preco',
        'arquivo',
        'capa',
        'status',
        'visualizacoes'
    ];

    protected $casts = [
        'nivel' => 'array',
    ];

    const STATUS = ['Ativo', 'Inativo', 'Pendente'];

    public function explicador()
    {
        return $this->belongsTo(User::class, 'explicador_id');
    }

    public function vendas()
    {
        return $this->hasMany(Biblioteca::class);
    }

    public function avaliacoes()
    {
        return $this->hasMany(Avaliacao::class);
    }
}
