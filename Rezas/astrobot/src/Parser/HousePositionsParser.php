<?php

declare(strict_types=1);

namespace Astroinfo\App\Parser;

use Astroinfo\App\HousePosition;
use DiDom\Document;
use DiDom\Element;
use RuntimeException;

final class HousePositionsParser
{
    /**
     * Parses 12 houses split across 2 ".vypocet-planet" blocks.
     *
     * @return HousePosition[]
     */
    public function parseFromHousesHtml(string $html): array
    {
        $doc = new Document($html);

        // Each "house row" starts with a left column that contains the house number anchor: #dum-1 ... #dum-12
        $leftNodes = $doc->find('.dum-left, .dum-left_2');

        if (\count($leftNodes) === 0)
        {
            throw new RuntimeException('No house left nodes found: .dum-left or .dum-left_2');
        }

        $result = [];

        foreach ($leftNodes as $left)
        {
            $house = $this->parseHouseNumber($left);
            if ($house === null)
            {
                continue;
            }

            // The expected structure is: left node followed by the "dum-middle" node containing sign and degree
            $middle = $this->findNextElementSibling($left);
            if ($middle === null)
            {
                continue;
            }

            if (!$this->hasClass($middle, 'dum-middle'))
            {
                continue;
            }

            $sign = $this->parseSignFromMiddle($middle);
            [$deg, $min, $sec] = $this->parseDmsFromMiddle($middle);
            $angleLabel = $this->parseAngleLabelFromMiddle($middle);

            if ($sign === '')
            {
                continue;
            }

            $result[$house] = new HousePosition(
                $house,
                $sign,
                $deg,
                $min,
                $sec,
                $angleLabel
            );
        }

        // Ensure we return in house order 1..12 (if present)
        ksort($result);

        return array_values($result);
    }

    private function parseHouseNumber(Element $left): ?int
    {
        // Example: <a href="#dum-1">1</a>
        $a = $left->first('a');
        if ($a === null)
        {
            return null;
        }

        $value = $this->normalizeText($a->text());
        $value = preg_replace('/[^\d]/', '', $value) ?? '';

        if ($value === '')
        {
            return null;
        }

        $n = (int)$value;

        if ($n < 1 || $n > 12)
        {
            return null;
        }

        return $n;
    }

    private function parseSignFromMiddle(Element $middle): string
    {
        // Example:
        // <div class="dum-znameni"><img ... alt="Aquarius">Aquarius<span class="form-info"> (ASC)</span></div>
        $signEl = $middle->first('.dum-znameni');
        if ($signEl === null)
        {
            return '';
        }

        // Prefer img alt when available because it is clean and stable
        $img = $signEl->first('img');
        if ($img !== null)
        {
            $alt = (string)$img->getAttribute('alt');
            $alt = $this->normalizeText($alt);

            if ($alt !== '')
            {
                return $alt;
            }
        }

        // Fallback: remove form-info label from text
        $cloneText = $this->normalizeText($signEl->text());
        $cloneText = preg_replace('/\(\s*[A-Z]+\s*\)/u', '', $cloneText) ?? $cloneText;
        $cloneText = $this->normalizeText($cloneText);

        return $cloneText;
    }

    /**
     * Houses often come with only degrees and minutes (no seconds).
     * Example: 28°56’
     *
     * Returns [degree, minute, second]
     */
    private function parseDmsFromMiddle(Element $middle): array
    {
        $degEl = $middle->first('.dum-right');
        if ($degEl === null)
        {
            return [0, 0, 0];
        }

        $text = $this->normalizeText($degEl->text());

        // Normalize primes to plain ascii for easier regex
        $text = str_replace(["’", "''", "″", "“", "”", "´", "`"], ["'", "''", '"', '"', '"', "'", "'"], $text);

        // Typical: "28°56'"
        if (preg_match('/(\d+)\s*°\s*(\d+)\s*\'/u', $text, $m) === 1)
        {
            return [(int)$m[1], (int)$m[2], 0];
        }

        // Sometimes: "28°56'12''"
        if (preg_match('/(\d+)\s*°\s*(\d+)\s*\'\s*(\d+)/u', $text, $m) === 1)
        {
            return [(int)$m[1], (int)$m[2], (int)$m[3]];
        }

        // Fallback: only degree
        if (preg_match('/(\d+)\s*°/u', $text, $m) === 1)
        {
            return [(int)$m[1], 0, 0];
        }

        return [0, 0, 0];
    }

    private function parseAngleLabelFromMiddle(Element $middle): ?string
    {
        // Example: <span class="form-info"> (ASC)</span>
        $info = $middle->first('.dum-znameni .form-info');
        if ($info === null)
        {
            return null;
        }

        $label = $this->normalizeText($info->text());
        $label = trim($label, '() ');
        $label = $this->normalizeText($label);

        if ($label === '')
        {
            return null;
        }

        return $label;
    }

    private function findNextElementSibling(Element $el): ?Element
    {
        // DiDom does not expose a simple "nextElementSibling" helper directly, so we use the underlying DOM node.
        $node = $el->getNode();
        $next = $node->nextSibling;

        while ($next !== null)
        {
            if ($next->nodeType === XML_ELEMENT_NODE)
            {
                return new Element($next);
            }

            $next = $next->nextSibling;
        }

        return null;
    }

    private function hasClass(Element $el, string $class): bool
    {
        $classAttr = (string)$el->getAttribute('class');
        $classAttr = ' ' . $classAttr . ' ';

        return strpos($classAttr, ' ' . $class . ' ') !== false;
    }

    private function normalizeText(string $value): string
    {
        // Remove NBSP and trim
        $value = str_replace("\xc2\xa0", ' ', $value);
        $value = trim($value);

        // Collapse whitespace
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        // Remove trailing ":" just in case
        $value = rtrim($value, ':');

        return $value;
    }
}
