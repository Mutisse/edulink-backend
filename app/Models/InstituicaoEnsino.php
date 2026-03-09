<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstituicaoEnsino extends Model
{
    use HasFactory;

    protected $table = 'instituicoes_ensino';

    protected $fillable = [
        'nome',
        'tipo',
        'nivel_ensino_id',
        'endereco',
        'latitude',
        'longitude',
        'telefone',
        'website',
        'ativo'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'ativo' => 'boolean'
    ];

    public function nivelEnsino()
    {
        return $this->belongsTo(NivelEnsino::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'instituicao_id');
    }

    public function scopeProximas($query, $lat, $lng, $raio = 10)
    {
        return $query->selectRaw("
            *,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) *
            cos(radians(longitude) - radians(?)) + sin(radians(?)) *
            sin(radians(latitude)))) AS distancia", [$lat, $lng, $lat])
            ->having('distancia', '<', $raio)
            ->orderBy('distancia');
    }
}
