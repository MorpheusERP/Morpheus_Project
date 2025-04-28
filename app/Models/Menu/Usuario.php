<?php

namespace App\Models\Menu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

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
        'nome_Usuario',
        'email',
        'tipo_Usuario',
        'senha',
        'nivel_Usuario',
        'sobrenome',
        'funcao'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'senha',
    ];

    /**
     * Hash the password before saving.
     *
     * @param string $value
     * @return void
     */
    public function setSenhaAttribute($value)
    {
        $this->attributes['senha'] = Hash::make($value);
    }
}