<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracaoSistema extends Model
{
    use HasFactory;

    protected $table = 'configuracoes_sistema';

    protected $fillable = [
        'chave',
        'valor',
        'tipo',
        'descricao'
    ];

    protected $casts = [
        'valor' => 'json'
    ];

    public static function get($chave, $padrao = null)
    {
        $config = self::where('chave', $chave)->first();
        return $config ? $config->valor : $padrao;
    }

    public static function set($chave, $valor, $tipo = 'string', $descricao = '')
    {
        return self::updateOrCreate(
            ['chave' => $chave],
            [
                'valor' => $valor,
                'tipo' => $tipo,
                'descricao' => $descricao
            ]
        );
    }
}
