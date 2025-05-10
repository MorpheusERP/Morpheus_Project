<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalDestino extends Model
{
    use HasFactory;

    protected $table = 'local_destinos';
    protected $primaryKey = 'id_Local';
    public $timestamps = true;

    protected $fillable = [
        'nome_Local',
        'tipo_Local',
        'observacao',
    ];
}
