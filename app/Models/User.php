<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The primary key associated with the table.
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
        'senha',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'senha',
        'remember_token',
    ];

    /**
     * The attribute name for the password.
     *
     * @var string
     */
    protected $passwordName = 'senha';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->senha;
    }

    /**
     * Set the user's password (senha).
     * For consistency, we'll hash all passwords
     */
    public function setSenhaAttribute($value)
    {
        // Check if the value is already hashed
        if (substr($value, 0, 4) === '$2y$') {
            $this->attributes['senha'] = $value;
        } else {
            // Hash the password before storing
            $this->attributes['senha'] = Hash::make($value);
        }
    }
    
    /**
     * Check if the given plain text password matches the user's password
     *
     * @param string $password
     * @return bool
     */
    public function checkPassword($password)
    {
        // If senha is hashed (starts with $2y$)
        if (substr($this->senha, 0, 4) === '$2y$') {
            return Hash::check($password, $this->senha);
        }
        
        // Direct comparison for non-hashed passwords
        return $password === $this->senha;
    }
}