<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biblioteca extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudante_id',
        'conteudo_id',
        'tipo',
        'progresso',
        'favorito',
        'ultimo_acesso'
    ];

    protected $casts = [
        'favorito' => 'boolean',
        'progresso' => 'integer',
        'ultimo_acesso' => 'datetime'
    ];

    public function estudante()
    {
        return $this->belongsTo(User::class, 'estudante_id');
    }

    public function conteudo()
    {
        return $this->belongsTo(Conteudo::class);
    }
}
