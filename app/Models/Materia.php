<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    use HasFactory;

    protected $table = 'materias';

    protected $fillable = [
        'nome',
        'icone',
        'cor',
        'ativo',
        'ordem'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'ordem' => 'integer'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'materia_id');
    }

    public function conteudos()
    {
        return $this->hasMany(Conteudo::class, 'materia_id');
    }

    public function explicadores()
    {
        return $this->belongsToMany(Explicador::class, 'explicador_materias');
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}
