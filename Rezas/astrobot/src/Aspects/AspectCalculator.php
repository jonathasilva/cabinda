<?php

declare(strict_types=1);

namespace Astroinfo\App\Aspects;

use Astroinfo\App\HousePosition;
use Astroinfo\App\PlanetPosition;

final class AspectCalculator
{
    /**
     * Traditional aspects by sign distance:
     * 0  = conjunction
     * 2  = sextile
     * 3  = square
     * 4  = trine
     * 6  = opposition
     */
    private const array SignAspectMap =
    [
        0 => ['name' => 'Conjunction', 'degrees' => 0],
        2 => ['name' => 'Sextile', 'degrees' => 60],
        3 => ['name' => 'Square', 'degrees' => 90],
        4 => ['name' => 'Trine', 'degrees' => 120],
        6 => ['name' => 'Opposition', 'degrees' => 180],
    ];

    /**
     * Default moiety orbs (in degrees) for planets/points.
     * Values here are a practical traditional set; adjust to your tradition.
     *
     * These are FULL orbs /2? No: These are moiety (half-orb) directly.
     * Example: Sun orb 15° => moiety 7.5°
     */
    private const array DefaultMoietyOrbs =
    [
        'Sun'     => 7.5,
        'Moon'    => 6.0,
        'Mercury' => 3.5,
        'Venus'   => 4.0,
        'Mars'    => 3.75,
        'Jupiter' => 4.5,
        'Saturn'  => 4.0,

        // Optional/modern points (choose values you prefer)
        'Uranus'  => 2.0,
        'Neptune' => 2.0,
        'Pluto'   => 2.0,
        'Node'    => 2.0,
        'Lilith'  => 1.5,
        'Chiron'  => 1.5,

        // Angles (often treated strong; set as desired)
        'ASC'     => 5.0,
        'MC'      => 5.0,
        'DESC'    => 5.0,
        'IC'      => 5.0,
    ];

    /**
     * Speed ranks for applying/separating.
     * Higher = faster mover (used as "applying planet").
     */
    private const array SpeedRank =
    [
        'Moon'    => 100,
        'Mercury' => 90,
        'Venus'   => 80,
        'Sun'     => 70,
        'Mars'    => 60,
        'Jupiter' => 50,
        'Saturn'  => 40,

        // Optional/modern (slow)
        'Uranus'  => 20,
        'Neptune' => 15,
        'Pluto'   => 10,

        // Points/angles (treat as slow/fixed)
        'ASC'     => 0,
        'MC'      => 0,
        'DESC'    => 0,
        'IC'      => 0,
        'Node'    => 0,
        'Lilith'  => 0,
        'Chiron'  => 25,
    ];

    /**
     * @param PlanetPosition[] $planets
     * @param HousePosition[] $houses
     * @return AspectResult[]
     */
    public function calculate(array $planets, array $houses): array
    {
        $points = $this->buildPoints($planets, $houses);

        $results = [];

        $count = \count($points);

        for ($i = 0; $i < $count; $i++)
        {
            for ($j = $i + 1; $j < $count; $j++)
            {
                $a = $points[$i];
                $b = $points[$j];

                $aspect = $this->tryBuildAspect($a, $b);

                if ($aspect !== null)
                {
                    $results[] = $aspect;
                }
            }
        }

        return $results;
    }

    /**
     * @param PlanetPosition[] $planets
     * @param HousePosition[] $houses
     * @return AspectPoint[]
     */
    private function buildPoints(array $planets, array $houses): array
    {
        $points = [];

        foreach ($planets as $p)
        {
            $name = $p->Planet;

            $points[] = new AspectPoint(
                $name,
                $p->Sign,
                $p->SignZodiacPosition,
                $p->Degree,
                $p->Minute,
                $p->Second,
                true,
                $this->speedRankFor($name),
                $this->moietyOrbFor($name)
            );
        }

        // Angular houses: 1,4,7,10
        $angular =
            [
                1 => 'ASC',
                4 => 'IC',
                7 => 'DESC',
                10 => 'MC',
            ];

        foreach ($houses as $h)
        {
            if (!isset($angular[$h->House]))
            {
                continue;
            }

            $angleName = $angular[$h->House];

            $points[] = new AspectPoint(
                $angleName,
                $h->Sign,
                $this->signBaseDegree($h->Sign),
                $h->Degree,
                $h->Minute,
                $h->Second,
                false,
                $this->speedRankFor($angleName),
                $this->moietyOrbFor($angleName)
            );
        }

        return $points;
    }

    private function tryBuildAspect(AspectPoint $a, AspectPoint $b): ?AspectResult
    {
        $signDistance = $this->signDistance($a->Sign, $b->Sign);

        if (!isset(self::SignAspectMap[$signDistance]))
        {
            // Not a traditional Ptolemaic sign aspect
            return null;
        }

        $aspectName = self::SignAspectMap[$signDistance]['name'];
        $aspectDegrees = self::SignAspectMap[$signDistance]['degrees'];

        $exactDeltaDeg = $this->exactDeltaDegrees($a, $b, $aspectDegrees);

        // Moiety rule: allowed distance = moiety(A) + moiety(B)
        $allowed = $a->MoietyOrbDeg + $b->MoietyOrbDeg;

        // State:
        // - Active: within moiety
        // - Activating: sign-aspect exists but still outside moiety (optional but you asked for it)
        // - Terminated: you may choose to drop it entirely; here we keep it if sign-aspect exists,
        //   but mark as Terminated when far beyond moiety.
        $state = ($exactDeltaDeg <= $allowed)
            ? 'Active'
            : 'Activating';

        // Phase (traditional-ish): applying/exact/separating
        // We determine which point is the "applying" one (faster).
        $phase = $this->phaseApplyingExactSeparating($a, $b, $aspectDegrees);

        // If you want "Terminated" (beyond moiety) instead of "Activating":
        // You can set a rule like: if delta > allowed => Terminated.
        // But since you asked for all three: active, activating, terminado,
        // we can classify:
        // - Active: delta <= allowed
        // - Activating: allowed < delta <= allowed + X (optional)
        // - Terminated: delta > allowed + X
        //
        // For now, "Activating" means "sign-aspect exists but not within moiety".
        // If you want Terminated too, define a second threshold.
        $state = $this->threeState($exactDeltaDeg, $allowed);

        return new AspectResult(
            $a->Name,
            $b->Name,
            $aspectName,
            $aspectDegrees,
            $signDistance,
            $exactDeltaDeg,
            $allowed,
            $state,
            $phase
        );
    }

    private function threeState(float $delta, float $allowed): string
    {
        // You asked for: active, activating, terminated.
        // Here is a simple traditional-friendly split:
        // - Active: within moiety
        // - Activating: outside moiety but within 2x moiety (approaching/loosely in range)
        // - Terminated: beyond that
        //
        // Adjust multiplier to your preference.
        if ($delta <= $allowed)
        {
            return 'Active';
        }

        if ($delta <= ($allowed * 2.0))
        {
            return 'Activating';
        }

        return 'Terminated';
    }

    private function phaseApplyingExactSeparating(AspectPoint $a, AspectPoint $b, int $aspectDegrees): string
    {
        // Decide which is faster
        $fast = $a;
        $slow = $b;

        if ($b->SpeedRank > $a->SpeedRank)
        {
            $fast = $b;
            $slow = $a;
        }

        $diff = $this->signedSeparationDegrees($fast->longitudeAsFloat(), $slow->longitudeAsFloat());

        // For an aspect of X degrees:
        // - Applying if current separation < X
        // - Exact if equal (within epsilon)
        // - Separating if current separation > X
        //
        // Note: This assumes direct motion for "fast". For retrograde realism, we need speed sign.
        // You can extend this later using $planet->Motion and numeric speed.
        $epsilon = 1e-6;

        if (abs($diff - $aspectDegrees) <= $epsilon)
        {
            return 'Exact';
        }

        if ($diff < $aspectDegrees)
        {
            return 'Applying';
        }

        return 'Separating';
    }

    private function exactDeltaDegrees(AspectPoint $a, AspectPoint $b, int $aspectDegrees): float
    {
        $sep = $this->absoluteSeparationDegrees($a->longitudeAsFloat(), $b->longitudeAsFloat());

        return abs($sep - $aspectDegrees);
    }

    private function absoluteSeparationDegrees(float $lonA, float $lonB): float
    {
        $d = abs($lonA - $lonB);

        if ($d > 180.0)
        {
            $d = 360.0 - $d;
        }

        return $d;
    }

    private function signedSeparationDegrees(float $lonFast, float $lonSlow): float
    {
        // Returns separation in [0..360) going forward from slow -> fast
        $d = $lonFast - $lonSlow;

        while ($d < 0.0)
        {
            $d += 360.0;
        }

        while ($d >= 360.0)
        {
            $d -= 360.0;
        }

        // For aspects we usually care about the "short arc" for phase checks,
        // but applying/separating convention here uses the direct distance in zodiac order.
        // For classical aspects (<= 180) we map to 0..180 (short arc).
        if ($d > 180.0)
        {
            $d = 360.0 - $d;
        }

        return $d;
    }

    private function signDistance(string $signA, string $signB): int
    {
        $a = $this->signIndex($signA);
        $b = $this->signIndex($signB);

        $diff = abs($a - $b);

        if ($diff > 6)
        {
            $diff = 12 - $diff;
        }

        return $diff;
    }

    private function signIndex(string $sign): int
    {
        $order =
            [
                'Aries'       => 0,
                'Taurus'      => 1,
                'Gemini'      => 2,
                'Cancer'      => 3,
                'Leo'         => 4,
                'Virgo'       => 5,
                'Libra'       => 6,
                'Scorpio'     => 7,
                'Sagittarius' => 8,
                'Capricorn'   => 9,
                'Aquarius'    => 10,
                'Pisces'      => 11,
            ];

        return $order[$sign] ?? 0;
    }

    private function signBaseDegree(string $sign): int
    {
        $bases =
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

        return $bases[$sign] ?? 0;
    }

    private function moietyOrbFor(string $name): float
    {
        return self::DefaultMoietyOrbs[$name] ?? 2.0;
    }

    private function speedRankFor(string $name): int
    {
        return self::SpeedRank[$name] ?? 0;
    }
}
