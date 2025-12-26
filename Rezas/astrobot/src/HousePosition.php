<?php

declare(strict_types=1);

namespace Astroinfo\App;

final class HousePosition
{
    public int $House;

    public string $Sign;

    public int $Degree;
    public int $Minute;
    public int $Second;

    public string $Position;

    public int $DegreeTotal;
    public int $MinuteTotal;
    public int $SecondTotal;

    public string $PositionTotal;

    public ?string $AngleLabel;

    public function __construct(
        int $house,
        string $sign,
        int $degree,
        int $minute,
        int $second,
        ?string $angleLabel = null
    )
    {
        $this->House = $house;
        $this->Sign = $sign;

        $this->Degree = $degree;
        $this->Minute = $minute;
        $this->Second = $second;

        // Degreeº Minute'Second''
        $this->Position = sprintf('%dº %d\' %d\'\'', $degree, $minute, $second);

        $this->AngleLabel = $angleLabel;

        $this->calculateTotalPosition();
    }

    private function calculateTotalPosition(): void
    {
        // Each sign has 30 degrees
        $signs =
            [
                'Aries'       => 0,
                'Taurus'      => 30,
                'Gemini'      => 60,
                'Cancer'      => 90,
                'Leo'         => 120,
                'Virgo'       => 150,
                'Libra'       => 180,
                'Scorpio'     => 210,
                'Sagittarius' => 240,
                'Capricorn'   => 270,
                'Aquarius'    => 300,
                'Pisces'      => 330,
            ];

        $baseDegree = $signs[$this->Sign] ?? 0;

        $this->DegreeTotal = $baseDegree + $this->Degree;
        $this->MinuteTotal = $this->Minute;
        $this->SecondTotal = $this->Second;

        $this->PositionTotal = sprintf(
            '%dº %d\' %d\'\'',
            $this->DegreeTotal,
            $this->MinuteTotal,
            $this->SecondTotal
        );
    }
}
