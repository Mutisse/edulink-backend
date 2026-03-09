<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
 protected $table = 'feedbacks';
    protected $fillable = [
        'user_id',
        'tipo',
        'texto',
        'resposta',
        'status'
    ];

    const TIPOS = ['Sugestão', 'Reclamação', 'Dúvida', 'Elogio'];
    const STATUS = ['Pendente', 'Respondido', 'Resolvido'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
