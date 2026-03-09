<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrinho extends Model
{
    use HasFactory;

    protected $fillable = [
        'estudante_id',
        'conteudo_id',
        'quantidade'
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
