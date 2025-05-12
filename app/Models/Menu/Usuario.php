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

    protected $casts = [
        'id_Usuario' => 'integer',
        'nome_Usuario' => 'string',
        'email' => 'string',
        'tipo_Usuario' => 'string',
        'senha' => 'string',
        'nivel_Usuario' => 'string',
        'sobrenome' => 'string',
        'funcao' => 'string',
    ];

    /**
     * Hash the password before saving, only if not already hashed.
     *
     * @param string $value
     * @return void
     */
    public function setSenhaAttribute($value)
    {
        if (!empty($value) && !Hash::needsRehash($value)) {
            $this->attributes['senha'] = Hash::make($value);
        } else {
            $this->attributes['senha'] = $value;
        }
    }
}