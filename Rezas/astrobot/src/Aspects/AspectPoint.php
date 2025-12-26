<?php

declare(strict_types=1);

namespace Astroinfo\App\Aspects;

final class AspectPoint
{
    public string $Name;          // "Sun", "Moon", "ASC", "MC", etc.
    public string $Sign;          // "Capricorn"
    public int $SignZodiacBase;   // 270 (Capricorn base)
    public int $Degree;           // within sign
    public int $Minute;
    public int $Second;
    public int $LongitudeTotal;   // 0..359 (or 0..360) in degrees, integer precision here
    public bool $IsPlanet;
    public int $SpeedRank;        // Higher = faster (used for applying/separating)
    public float $MoietyOrbDeg;   // Moiety orb in degrees (half orb)

    public function __construct(
        string $name,
        string $sign,
        int $signZodiacBase,
        int $degree,
        int $minute,
        int $second,
        bool $isPlanet,
        int $speedRank,
        float $moietyOrbDeg
    )
    {
        $this->Name = $name;
        $this->Sign = $sign;
        $this->SignZodiacBase = $signZodiacBase;

        $this->Degree = $degree;
        $this->Minute = $minute;
        $this->Second = $second;

        $this->LongitudeTotal = $signZodiacBase + $degree;

        $this->IsPlanet = $isPlanet;
        $this->SpeedRank = $speedRank;
        $this->MoietyOrbDeg = $moietyOrbDeg;
    }

    public function longitudeAsFloat(): float
    {
        return $this->SignZodiacBase
            + $this->Degree
            + ($this->Minute / 60.0)
            + ($this->Second / 3600.0);
    }
}
