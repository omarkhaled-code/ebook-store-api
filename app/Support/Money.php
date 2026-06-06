<?php

namespace App\Support;

final class Money
{
    public static function toCents(int|float|string $amount): int
    {
        $value = trim((string) $amount);

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $value)) {
            throw new \InvalidArgumentException('Invalid money amount.');
        }

        [$whole, $decimal] = array_pad(explode('.', $value, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($decimal, 0, 2), 2, '0');
    }
}
