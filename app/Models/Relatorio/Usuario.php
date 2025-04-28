<?php

namespace App\Models\Relatorio;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_Usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nivel_Usuario',
        'nome_Usuario',
        'sobrenome',
        'funcao',
        'email',
        'tipo_Usuario',
        'senha'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'senha',
    ];
}