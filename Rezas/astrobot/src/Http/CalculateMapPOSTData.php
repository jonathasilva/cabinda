<?php

namespace Astroinfo\App\Http;

final class CalculateMapPOSTData
{
    public static function string(string $key): string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
        if (!is_string($value))
        {
            return '';
        }

        return trim(self::stripControlChars($value));
    }

    private static function stripControlChars(string $value): string
    {
        // Remove control chars except \r \n \t
        return preg_replace('/[^\P{C}\r\n\t]+/u', '', $value) ?? '';
    }
}
