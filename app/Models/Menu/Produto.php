<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table = 'produtos';
    protected $primaryKey = 'cod_Produto';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = [
        'cod_Produto',
        'imagem',
        'nome_Produto',
        'tipo_Produto',
        'cod_Barras',
        'preco_Custo',
        'preco_Venda',
        'grupo',
        'sub_Grupo',
        'observacao',
    ];
}