<?php

namespace App\Enums;

enum TipoLocalDestino: string
{
    case DESCARTE = 'descarte';
    case REAPROVEITAMENTO = 'reaproveitamento';

    public function label(): string
    {
        return match ($this) {
            self::DESCARTE => 'Descarte',
            self::REAPROVEITAMENTO => 'Reaproveitamento',
        };
    }
    public static function toSelectArray(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $enum) {
            return [$enum->value => $enum->label()];
        })->toArray();
    }
}
