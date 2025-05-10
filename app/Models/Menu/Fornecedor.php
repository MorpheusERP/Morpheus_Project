<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';
    protected $primaryKey = 'id_Fornecedor';
    public $timestamps = true;

    protected $fillable = [
        'razao_Social',
        'nome_Fantasia',
        'apelido',
        'grupo',
        'sub_Grupo',
        'observacao',
    ];
}
