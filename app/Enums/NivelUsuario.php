<?php

namespace App\Enums;

enum NivelUsuario: string
{
    case DEFAULT = 'padrao';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::DEFAULT => 'Padrão',
            self::ADMIN => 'Administrador',
        };
    }

    public static function toSelectArray(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $enum) {
            return [$enum->value => $enum->label()];
        })->toArray();
    }
}
