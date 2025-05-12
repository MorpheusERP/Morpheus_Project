<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Model;

class SaidaProduto extends Model
{
    protected $table = 'saida_produtos';
    protected $primaryKey = 'id_Saida';
    public $timestamps = true;

    protected $fillable = [
        'imagem',
        'id_Usuario',
        'nome_Usuario',
        'cod_Produto',
        'nome_Produto',
        'preco_Custo',
        'id_Local',
        'nome_Local',
        'id_Estoque',
        'qtd_Saida',
        'valor_Total',
        'observacao',
        'data_Saida',
    ];
}
