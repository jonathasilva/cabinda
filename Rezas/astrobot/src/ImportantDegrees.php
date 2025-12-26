<?php

declare(strict_types=1);

namespace Astroinfo\App;

final class ImportantDegrees
{
    /**
     * @return list<string>
     */
    public function computeForPlanet(PlanetPosition $position): array
    {
        $name = $position->Planet;

        return $this->compute(
            name: $name,
            sign: $position->Sign,
            degree: $position->Degree,
            minute: $position->Minute
        );
    }

    /**
     * @return list<string>
     */
    public function computeForHouse(HousePosition $position): array
    {
        $name = $this->formatHouseName($position);

        return $this->compute(
            name: $name,
            sign: $position->Sign,
            degree: $position->Degree,
            minute: $position->Minute
        );
    }

    /**
     * @return list<string>
     */
    public function computeAll(array $positions, array $houses): array
    {
        $all = [];

        foreach ($positions as $position)
        {
            if (!$position instanceof PlanetPosition)
            {
                continue;
            }

            foreach ($this->computeForPlanet($position) as $message)
            {
                $all[] = $message;
            }
        }

        foreach ($houses as $house)
        {
            if (!$house instanceof HousePosition)
            {
                continue;
            }

            foreach ($this->computeForHouse($house) as $message)
            {
                $all[] = $message;
            }
        }

        return $all;
    }

    /**
     * @return list<string>
     */
    private function compute(string $name, string $sign, int $degree, int $minute): array
    {
        $info = [];

        $this->appendCriticalDegree($info, $name, $sign, $degree);
        $this->appendAnareticDegree($info, $name, $sign, $degree, $minute);
        $this->appendInitialDegree($info, $name, $sign, $degree, $minute);
        $this->appendTerminalDegree($info, $name, $sign, $degree, $minute);
        $this->appendCombustWay($info, $name, $sign, $degree, $minute);
        $this->appendMaleficZones($info, $name, $sign, $degree, $minute);
        $this->appendBeneficZones($info, $name, $sign, $degree, $minute);

        return $info;
    }

    /**
     * @param list<string> $info
     */
    private function appendCriticalDegree(array &$info, string $name, string $sign, int $degree): void
    {
        $criticalDegrees = $this->criticalDegreesBySignGroup($sign);

        if ($criticalDegrees === null)
        {
            return;
        }

        if (!in_array($degree, $criticalDegrees, true))
        {
            return;
        }

        $criticalStringTxt =
            "These degrees mark points of crisis and action."
            . "A planet at a critical degree is under pressure to act, and the matter it represents reaches a turning point."
            . "A significator at a critical degree indicates the situation is at a climax or an imminent decision."
            . "The crisis can be either positive or negative, but it certainly demands resolution.";

        $info[] = sprintf(
            "%s in %s at %d° is in a critical degree.\n\t%s ",
            $name,
            $sign,
            $degree,
            $criticalStringTxt
        );
    }

    /**
     * @param list<string> $info
     */
    private function appendAnareticDegree(array &$info, string $name, string $sign, int $degree, int $minute): void
    {
        if ($degree !== 29)
        {
            return;
        }

        $text =
            sprintf(
                "%s in %s at %d°%d ' is in a anaretic degree.",
                $name,
                $sign,
                $degree,
                $minute
            )
            . "\n\t"
            . sprintf(
                "The %s is at an anaretic degree, which is considered a critical degree, indicating a sense of urgency or finality in the matters related to this planet. This degree can also represent a final test or karmic situation.",
                $name
            );

        $info[] = $text;
    }

    /**
     * @param list<string> $info
     */
    private function appendInitialDegree(array &$info, string $name, string $sign, int $degree, int $minute): void
    {
        if ($degree !== 0)
        {
            return;
        }

        $text =
            sprintf(
                "%s in %s at %d°%d' is in a initial/start degree.",
                $name,
                $sign,
                $degree,
                $minute
            )
            . "\n\t"
            . sprintf(
                "The %s is at an initial degree, representing a beginning, uncertainty, or even great raw energy if the planet is strong.",
                $name
            );

        $info[] = $text;
    }

    /**
     * @param list<string> $info
     */
    private function appendTerminalDegree(array &$info, string $name, string $sign, int $degree, int $minute): void
    {
        if (!in_array($degree, [27, 28], true))
        {
            return;
        }

        $text =
            sprintf(
                "%s in %s at %d°%d' is in a terminal degree.",
                $name,
                $sign,
                $degree,
                $minute
            )
            . "\n\t"
            . sprintf(
                "The %s is at a terminal degree, which has the nature of an \"end of cycle\" but with less desperation and more resignation. It indicates that a situation is clearly coming to a close. There is little that can be done to change the course of events. The matter is \"past its prime,\" losing strength and relevance. It suggests that something will not last or that interest is fading.",
                $name
            );

        $info[] = $text;
    }

    /**
     * @param list<string> $info
     */
    private function appendCombustWay(array &$info, string $name, string $sign, int $degree, int $minute): void
    {
        $isCombustWay =
            ($sign === 'Libra' && $degree >= 15)
            || ($sign === 'Scorpio' && $degree < 15);

        if (!$isCombustWay)
        {
            return;
        }

        $isSavedBySpica = $sign === 'Libra' && in_array($degree, [23, 24], true);

        if ($isSavedBySpica)
        {
            $text =
                sprintf(
                    "%s in %s at %d°%d' is in the combust way but saved by Spica",
                    $name,
                    $sign,
                    $degree,
                    $minute
                )
                . "\n\t"
                . sprintf(
                    "The %s is in the combust way of the map, but it is saved by Spica, which is a very fortunate star. This position deny the combust way",
                    $name
                );

            $info[] = $text;

            return;
        }

        $text =
            sprintf(
                "%s in %s at %d°%d' is in the combust way",
                $name,
                $sign,
                $degree,
                $minute
            )
            . "\n\t"
            . "It is considered a zone of extreme misfortune and debility. A planet (especially the Moon) in this section of the zodiac is severely afflicted, as if it were \"burned\" or on a \"road of fire.\" This can corrupt judgment and lead the matter to a bad outcome, regardless of other factors. If the Ascendant of the horary chart falls here, the question itself may be asked out of desperation, the chart may not be radical (not fit for judgment), or the querent may be in a very difficult and unclear situation.";

        $info[] = $text;
    }

    /**
     * @param list<string> $info
     */
    private function appendMaleficZones(array &$info, string $name, string $sign, int $degree, int $minute): void
    {
        $zone = $this->maleficZoneFor($sign, $degree);

        if ($zone === null)
        {
            return;
        }

        $text =
            sprintf(
                "%s in %s at %d°%d' is in the melefic zone",
                $name,
                $sign,
                $degree,
                $minute
            )
            . "\n\t"
            . sprintf(
                "The %s is in the melefic zone of the map, which is a very unfortunate star. %s",
                $name,
                $zone
            );

        $info[] = $text;
    }

    /**
     * @param list<string> $info
     */
    private function appendBeneficZones(array &$info, string $name, string $sign, int $degree, int $minute): void
    {
        $zone = $this->beneficZoneFor($sign, $degree);

        if ($zone === null)
        {
            return;
        }

        $text =
            sprintf(
                "%s in %s at %d°%d' is in a benefic zone",
                $name,
                $sign,
                $degree,
                $minute
            )
            . "\n\t"
            . sprintf(
                "The %s is in a benefic zone of the map. %s.",
                $name,
                $zone
            );

        $info[] = $text;
    }

    /**
     * @return list<int>|null
     */
    private function criticalDegreesBySignGroup(string $sign): ?array
    {
        $cardinal = ['Aries', 'Cancer', 'Libra', 'Capricorn'];
        $fixed = ['Taurus', 'Leo', 'Scorpio', 'Aquarius'];
        $mutable = ['Gemini', 'Virgo', 'Sagittarius', 'Pisces'];

        if (in_array($sign, $cardinal, true))
        {
            return [0, 13, 26];
        }

        if (in_array($sign, $fixed, true))
        {
            return [8, 9, 21, 22];
        }

        if (in_array($sign, $mutable, true))
        {
            return [4, 17];
        }

        return null;
    }

    private function maleficZoneFor(string $sign, int $degree): ?string
    {
        $zones =
            [
                'Aries' => [
                    9 => 'Violence, aggression',
                    15 => 'War, impulsiveness, mistakes',
                ],
                'Taurus' => [
                    9 => 'Inflexibility, material losses',
                ],
                'Gemini' => [
                    19 => 'Deceptions, falsehoods',
                ],
                'Cancer' => [
                    9 => 'Emotional vulnerability',
                ],
                'Leo' => [
                    15 => 'Pride, fall by vanity',
                ],
                'Virgo' => [
                    18 => 'Health problems and servitude',
                    23 => 'Slavery, servitude',
                ],
                'Libra' => [
                    9 => 'Faulty judgment',
                ],
                'Scorpio' => [
                    15 => 'Cruelty, obsession',
                ],
                'Sagittarius' => [
                    18 => 'False hope, recklessness',
                ],
                'Capricorn' => [
                    23 => 'Prison, limitation',
                ],
                'Aquarius' => [
                    9 => 'Alienation, coldness',
                ],
                'Pisces' => [
                    15 => 'Illusion, escape, emotional weakness',
                ],
            ];

        return $zones[$sign][$degree] ?? null;
    }

    private function beneficZoneFor(string $sign, int $degree): ?string
    {
        $zones =
            [
                'Aries' => [
                    3 => 'Courage and leadership',
                ],
                'Taurus' => [
                    14 => 'Prosperity and firmness',
                    27 => 'Material protection, fertility',
                ],
                'Gemini' => [
                    12 => 'Intelligence, communication',
                ],
                'Cancer' => [
                    21 => 'Positive sensitivity',
                ],
                'Leo' => [
                    11 => 'Nobility, recognition',
                ],
                'Virgo' => [
                    19 => 'Precision, order, health',
                ],
                'Libra' => [
                    22 => 'Justice, diplomacy',
                ],
                'Scorpio' => [
                    18 => 'Psychic strength, investigation',
                ],
                'Sagittarius' => [
                    15 => 'Wisdom, faith',
                ],
                'Capricorn' => [
                    28 => 'Strategy, solid ambition',
                ],
                'Aquarius' => [
                    4 => 'Innovation with responsibility',
                ],
                'Pisces' => [
                    24 => 'Inspiration, true compassion',
                ],
            ];

        return $zones[$sign][$degree] ?? null;
    }

    private function formatHouseName(HousePosition $position): string
    {
        if ($position->AngleLabel !== null && $position->AngleLabel !== '')
        {
            return $position->AngleLabel;
        }

        return sprintf('House %d', $position->House);
    }
}
