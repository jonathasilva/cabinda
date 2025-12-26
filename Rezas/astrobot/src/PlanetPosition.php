<?php

declare(strict_types=1);

namespace Astroinfo\App;

final class PlanetPosition
{
    public string $Planet;
    public string $Sign;

    public int $SignZodiacPosition;

    public int $Degree;
    public int $Minute;
    public int $Second;

    public string $Position;

    public int $DegreeTotal;
    public int $MinuteTotal;
    public int $SecondTotal;

    public string $PositionTotal;

    public int $House;

    public string $Motion;
    public string $Speed;

    /**
     * @var list<string>
     */
    public array $Dignities = [];

    private array $Signs = [
        'Aries' => 0,
        'Taurus' => 30,
        'Gemini' => 60,
        'Cancer' => 90,
        'Leo' => 120,
        'Virgo' => 150,
        'Libra' => 180,
        'Scorpio' => 210,
        'Sagittarius' => 240,
        'Capricorn' => 270,
        'Aquarius' => 300,
        'Pisces' => 330,
    ];

    public function __construct(
        string $planet,
        string $sign,
        int $degree,
        int $minute,
        int $second,
        int $house,
        string $motion,
        string $speed
    )
    {
        $this->Planet = $planet;
        $this->Sign = $sign;

        $this->Degree = $degree;
        $this->Minute = $minute;
        $this->Second = $second;

        // Degreeº Minute'Second''
        $this->Position = sprintf('%dº %d\' %d\'\'', $degree, $minute, $second);

        $this->House = $house;

        $this->Motion = $motion;
        $this->Speed = $speed;

        $this->calculateTotalPosition();
        $this->setSignZodiacPosition();
    }

    public function absoluteLongitudeDeg(): float
    {
        return $this->SignZodiacPosition
            + $this->Degree
            + ($this->Minute / 60)
            + ($this->Second / 3600);
    }

    public function applyCombustionFromSun(self $sunPosition): void
    {
        // Comments always in English: planets excluded from combustion logic (same as the JS rules)
        if (
            $this->Planet === 'Sun'
            || $this->Planet === 'Moon'
            || $this->Planet === 'Node'
            || $this->Planet === 'Lilith'
            || $this->Planet === 'Chiron'
            || $this->Planet === 'Pluto'
            || $this->Planet === 'Neptune'
            || $this->Planet === 'Uranus'
        )
        {
            return;
        }

        // Comments always in English: ensure Sun position looks valid
        if ($sunPosition->Sign === '')
        {
            return;
        }

        $planetLongitude = $this->absoluteLongitudeDeg();
        $sunLongitude = $sunPosition->absoluteLongitudeDeg();

        $distance = abs($planetLongitude - $sunLongitude);
        if ($distance > 180)
        {
            $distance = 360 - $distance;
        }

        // Comments always in English: Orbs in degrees
        $cazimiOrb = 0.2833333333; // 17'
        $combustOrb = 8.5;         // 8°30'
        $underBeamsOrb = 17.0;     // 17°

        if (
            $distance <= $cazimiOrb
            && ($this->Planet === 'Mercury' || $this->Planet === 'Venus')
        )
        {
            $this->Dignities[] = 'CAZIMI';
        }
        else if ($distance <= $combustOrb)
        {
            $this->Dignities[] = 'COMBUST';
        }
        else if ($distance <= $underBeamsOrb)
        {
            $this->Dignities[] = 'UNDER_BEAMS';
        }
    }

    private function calculateTotalPosition(): void
    {
        $baseDegree = $this->Signs[$this->Sign] ?? 0;

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

    private function setSignZodiacPosition(): void
    {
        $this->SignZodiacPosition = $this->Signs[$this->Sign] ?? throw new \InvalidArgumentException("Invalid sign: $this->Sign");
    }
}
