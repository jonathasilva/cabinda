<?php

declare(strict_types=1);

namespace Astroinfo\App;

use DateTimeImmutable;
use DateTimeZone;

final class ChartFormRequest
{
    public string $MapDate = '';
    public string $MapTime = '';

    public string $TransitDate = '';
    public string $TransitTime = '';

    public ?DateTimeImmutable $MapDateTime = null;
    public ?DateTimeImmutable $TransitDateTime = null;

    /** @var string[] */
    private array $Errors = [];

    public function __construct()
    {
        $this->MapDate = $this->postString('map_date');
        $this->MapTime = $this->postString('map_time');

        $this->TransitDate = $this->postString('transit_date');
        $this->TransitTime = $this->postString('transit_time');
    }

    public function isValid(): bool
    {
        $this->Errors = [];

        $this->MapDateTime = $this->toDateTimeImmutable($this->MapDate, $this->MapTime);
        if ($this->MapDateTime === null)
        {
            $this->Errors[] = 'Dados do mapa inválidos. Use data dd/mm/aaaa e hora hh:mm.';
        }

        $tDateFilled = $this->TransitDate !== '';
        $tTimeFilled = $this->TransitTime !== '';

        $this->TransitDateTime = null;

        if ($tDateFilled || $tTimeFilled)
        {
            if (!$tDateFilled || !$tTimeFilled)
            {
                $this->Errors[] = 'Trânsito incompleto. Preencha data e hora, ou deixe ambos em branco.';
            }
            else
            {
                $this->TransitDateTime = $this->toDateTimeImmutable($this->TransitDate, $this->TransitTime);
                if ($this->TransitDateTime === null)
                {
                    $this->Errors[] = 'Dados do trânsito inválidos. Use data dd/mm/aaaa e hora hh:mm.';
                }
            }
        }

        return count($this->Errors) === 0;
    }

    /** @return string[] */
    public function errors(): array
    {
        return $this->Errors;
    }

    private function postString(string $key): string
    {
        $value = filter_input(INPUT_POST, $key, FILTER_UNSAFE_RAW);
        if (!is_string($value))
        {
            return '';
        }

        $value = trim($value);

        // Remove control characters (except \r \n \t)
        $value = preg_replace('/[^\P{C}\r\n\t]+/u', '', $value) ?? '';

        return $value;
    }

    private function toDateTimeImmutable(string $dateBr, string $timeHm): ?DateTimeImmutable
    {
        $date = $this->parseDate($dateBr);
        $time = $this->parseTime($timeHm);

        if ($date === null || $time === null)
        {
            return null;
        }

        $tz = new DateTimeZone('America/Sao_Paulo');

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

    private function parseDate(string $value): ?array
    {
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

    private function parseTime(string $value): ?array
    {
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
}
