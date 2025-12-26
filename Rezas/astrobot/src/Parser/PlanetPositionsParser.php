<?php

declare(strict_types=1);

namespace Astroinfo\App\Parser;

use Astroinfo\App\PlanetPosition;
use DiDom\Document;
use DiDom\Element;
use RuntimeException;

final class PlanetPositionsParser
{
    /**
     * @return PlanetPosition[]
     */
    public function parseFromVypocetPlanetHtml(string $html): array
    {
        $doc = new Document($html);

        $rows = $doc->find('.horoskop-radek-kotva');

        if (\count($rows) === 0)
        {
            throw new RuntimeException('No rows found: .horoskop-radek-kotva');
        }

        $result = [];


        foreach ($rows as $row)
        {
            $cols = $this->elementChildren($row);

            // Expected columns:
            // 0: planet
            // 1: sign (.dum-znameni)
            // 2: degree (DMS)
            // 3: house
            // 4: motion
            // 5: speed
            if (\count($cols) < 6)
            {
                continue;
            }

            $planet = $this->parsePlanet($cols[0]);
            $sign = $this->parseSign($cols[1]);
            [$deg, $min, $sec] = $this->parseDms($cols[2]);
            $house = $this->parseInt($cols[3]->text());
            $motion = $this->normalizeText($cols[4]->text());
            $speed = $this->normalizeText($cols[5]->text());

            if ($planet === '' || $sign === '')
            {
                continue;
            }

            $result[] = new PlanetPosition(
                $planet,
                $sign,
                $deg,
                $min,
                $sec,
                $house,
                $motion,
                $speed
            );
        }

        return $result;
    }

    /**
     * Returns only element children (DOMElement), ignoring DOMText nodes.
     *
     * @return Element[]
     */
    private function elementChildren(Element $row): array
    {
        $children = $row->children();
        $elements = [];

        foreach ($children as $child)
        {
            // child is DiDom\Element wrapping either DOMElement or DOMText
            // We keep only actual elements (<div>, etc)
            if ($child->getNode()->nodeType === XML_ELEMENT_NODE)
            {
                $elements[] = $child;
            }
        }

        return $elements;
    }


    private function parsePlanet(Element $col): string
    {
        // Planet text is inside: <strong><a>Sun</a></strong>:
        $a = $col->first('a');
        if ($a !== null)
        {
            return $this->normalizeText($a->text());
        }

        $strong = $col->first('strong');
        if ($strong !== null)
        {
            return $this->normalizeText($strong->text());
        }

        // Fallback: from alt="Sun" in <img>
        $img = $col->first('img');
        if ($img !== null)
        {
            $alt = (string)$img->getAttribute('alt');
            $alt = $this->normalizeText($alt);
            if ($alt !== '')
            {
                return $alt;
            }
        }

        return '';
    }

    private function parseSign(Element $col): string
    {
        // Sign text is the element text: "Capricorn", "Pisces"...
        $text = $this->normalizeText($col->text());

        return $text;
    }

    /**
     * Returns [degree, minute, second]
     *
     * Input looks like:
     *   <span>4</span>°<span>59’28’’</span>
     */
    private function parseDms(Element $col): array
    {
        $text = $this->normalizeText($col->text());

        // Normalize primes to plain ascii for easier regex
        $text = str_replace(["’", "''", "″", "“", "”", "´", "`"], ["'", "''", '"', '"', '"', "'", "'"], $text);

        // Example after normalize: "4°59'28''"
        $deg = 0;
        $min = 0;
        $sec = 0;

        if (preg_match('/(\d+)\s*°\s*(\d+)\s*\'\s*(\d+)/u', $text, $m) === 1)
        {
            $deg = (int)$m[1];
            $min = (int)$m[2];
            $sec = (int)$m[3];

            return [$deg, $min, $sec];
        }

        // Fallback: sometimes seconds might be missing
        if (preg_match('/(\d+)\s*°\s*(\d+)\s*\'/u', $text, $m) === 1)
        {
            $deg = (int)$m[1];
            $min = (int)$m[2];

            return [$deg, $min, 0];
        }

        // Last fallback: only degree
        if (preg_match('/(\d+)\s*°/u', $text, $m) === 1)
        {
            return [(int)$m[1], 0, 0];
        }

        return [0, 0, 0];
    }

    private function parseInt(string $value): int
    {
        $value = $this->normalizeText($value);
        $value = preg_replace('/[^\d\-]/', '', $value) ?? '';

        if ($value === '' || $value === '-')
        {
            return 0;
        }

        return (int)$value;
    }

    private function normalizeText(string $value): string
    {
        // Remove NBSP and trim
        $value = str_replace("\xc2\xa0", ' ', $value);
        $value = trim($value);

        // Remove trailing ":" that appears after planet label
        $value = rtrim($value, ':');

        // Collapse whitespace
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }
}
