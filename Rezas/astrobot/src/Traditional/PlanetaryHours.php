<?php

declare(strict_types=1);

namespace Astroinfo\App\Traditional;

final class PlanetaryHours
{
    public string $DayPlanet;
    public string $HourPlanet;

    public ?string $LastHourPlanet;
    public ?string $LastHourOffset;

    public ?string $NextHourPlanet;
    public ?string $NextHourOffset;

    public ?string $HourLabel;

    public function __construct(
        string $dayPlanet,
        string $hourPlanet,
        ?string $lastHourPlanet,
        ?string $lastHourOffset,
        ?string $nextHourPlanet,
        ?string $nextHourOffset,
        ?string $hourLabel
    )
    {
        $this->DayPlanet = $dayPlanet;
        $this->HourPlanet = $hourPlanet;

        $this->LastHourPlanet = $lastHourPlanet;
        $this->LastHourOffset = $lastHourOffset;

        $this->NextHourPlanet = $nextHourPlanet;
        $this->NextHourOffset = $nextHourOffset;

        $this->HourLabel = $hourLabel;
    }
}
