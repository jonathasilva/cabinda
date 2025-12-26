<?php

declare(strict_types=1);

namespace Astroinfo\App\Validation;

use DateTimeImmutable;
use DateTimeZone;

final class BrDateTime
{
    public static function parseDate(string $value): ?array
    {
        // Expected: dd/mm/yyyy
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value))
        {
            return null;
        }

        [$d, $m, $y] = array_map('intval', explode('/', $value));

        if ($y < 1000 || $y > 3000)
        {
            return null;
        }

        if ($m < 1 || $m > 12)
        {
            return null;
        }

        $maxDay = (int)(new DateTimeImmutable(sprintf('%04d-%02d-01', $y, $m)))->format('t');
        if ($d < 1 || $d > $maxDay)
        {
            return null;
        }

        return ['day' => $d, 'month' => $m, 'year' => $y];
    }

    public static function parseTime(string $value): ?array
    {
        // Expected: hh:mm
        if (!preg_match('/^\d{2}:\d{2}$/', $value))
        {
            return null;
        }

        [$h, $min] = array_map('intval', explode(':', $value));

        if ($h < 0 || $h > 23)
        {
            return null;
        }

        if ($min < 0 || $min > 59)
        {
            return null;
        }

        return ['hour' => $h, 'minute' => $min];
    }

    public static function toDateTimeImmutable(
        string $dateBr,
        string $timeHm,
        ?DateTimeZone $tz = null
    ): ?DateTimeImmutable
    {
        $date = self::parseDate($dateBr);
        $time = self::parseTime($timeHm);

        if ($date === null || $time === null)
        {
            return null;
        }

        $tz = $tz ?? new DateTimeZone('America/Sao_Paulo');

        $iso = sprintf(
            '%04d-%02d-%02d %02d:%02d:00',
            $date['year'],
            $date['month'],
            $date['day'],
            $time['hour'],
            $time['minute']
        );

        $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $iso, $tz);
        if ($dt === false)
        {
            return null;
        }

        return $dt;
    }
}
