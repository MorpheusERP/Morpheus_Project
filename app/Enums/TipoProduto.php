<?php

namespace App\Enums;

enum TipoProduto: string
{
    case QUILO = 'quilo';
    case CAIXA = 'caixa';
    case UNIDADE = 'unidade';
    case SACO = 'saco';

    public function label(): string
    {
        return match ($this) {
            self::QUILO => 'Quilo (KG)',
            self::CAIXA => 'Caixa (CX)',
            self::UNIDADE => 'Unidade (UN)',
            self::SACO => 'Saco (SC)',
        };
    }
    public static function toSelectArray(): array
    {
        return collect(self::cases())->mapWithKeys(function (self $enum) {
            return [$enum->value => $enum->label()];
        })->toArray();
    }
}
