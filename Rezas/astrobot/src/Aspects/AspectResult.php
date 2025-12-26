<?php

declare(strict_types=1);

namespace Astroinfo\App\Aspects;

final class AspectResult
{
    public string $A;
    public string $B;

    public string $Aspect;        // "Conjunction", "Sextile", ...
    public int $AspectDegrees;    // 0, 60, 90, 120, 180

    public int $SignDistance;     // 0..6 (distance in signs)
    public float $ExactDeltaDeg;  // absolute distance from exact aspect (in degrees)

    public float $MoietyAllowedDeg;

    public string $State;         // "Active", "Activating", "Terminated"
    public string $Phase;         // "Applying", "Exact", "Separating"

    public function __construct(
        string $a,
        string $b,
        string $aspect,
        int $aspectDegrees,
        int $signDistance,
        float $exactDeltaDeg,
        float $moietyAllowedDeg,
        string $state,
        string $phase
    )
    {
        $this->A = $a;
        $this->B = $b;
        $this->Aspect = $aspect;
        $this->AspectDegrees = $aspectDegrees;
        $this->SignDistance = $signDistance;
        $this->ExactDeltaDeg = $exactDeltaDeg;
        $this->MoietyAllowedDeg = $moietyAllowedDeg;
        $this->State = $state;
        $this->Phase = $phase;
    }
}
