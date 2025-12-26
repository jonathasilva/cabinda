<?php

declare(strict_types=1);

namespace Astroinfo\App\Parser;

use Astroinfo\App\ElementBalance;
use DiDom\Document;
use DiDom\Element;
use RuntimeException;

final class ElementsParser
{
    /**
     * Parses the "Elements" table block (Fire/Earth/Air/Water).
     *
     * @return ElementBalance[]
     */
    public function parseFromElementsHtml(string $html): array
    {
        $doc = new Document($html);

        $rows = $doc->find('.horoskop-radek-kotva');

        if (\count($rows) === 0)
        {
            throw new RuntimeException('No rows found: .horoskop-radek-kotva (elements)');
        }

        $result = [];

        foreach ($rows as $row)
        {
            $cols = $this->elementChildren($row);

            // Expected columns:
            // 0: element label (Fire:)
            // 1: power (1x)
            // 2: planets text
            if (\count($cols) < 3)
            {
                continue;
            }

            $element = $this->parseElementName($cols[0]);
            $power = $this->parsePower($cols[1]->text());
            $planetsText = $this->normalizePlanetsText($cols[2]);

            if ($element === '')
            {
                continue;
            }

            $result[] = new ElementBalance($element, $power, $planetsText);
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
            if ($child->getNode()->nodeType === XML_ELEMENT_NODE)
            {
                $elements[] = $child;
            }
        }

        return $elements;
    }

    private function parseElementName(Element $col): string
    {
        // Example text: "Fire:" (may include extra whitespace)
        $text = $this->normalizeText($col->text());
        $text = rtrim($text, ':');
        $text = $this->normalizeText($text);

        // Keep it normalized to a stable title case
        // (Astro-Seek uses English labels already)
        return $text;
    }

    private function parsePower(string $value): int
    {
        // Example: "7x"
        $text = $this->normalizeText($value);
        $text = preg_replace('/[^\d\-]/', '', $text) ?? '';

        if ($text === '' || $text === '-')
        {
            return 0;
        }

        return (int)$text;
    }

    private function normalizePlanetsText(Element $col): string
    {
        // The column text might be like:
        // "Moon(2x), Jupiter, ... MC(2x)"
        // We want:
        // "Moon (2x), Jupiter, ... MC (2x)"
        $text = $this->normalizeText($col->text());

        // Ensure space before parentheses for readability
        $text = preg_replace('/([A-Za-z])\(/u', '$1 (', $text) ?? $text;

        // Normalize "(2x)" spacing
        $text = preg_replace('/\(\s*(\d+x)\s*\)/u', '($1)', $text) ?? $text;

        // Final trim / whitespace collapse
        $text = $this->normalizeText($text);

        return $text;
    }

    private function normalizeText(string $value): string
    {
        // Remove NBSP and trim
        $value = str_replace("\xc2\xa0", ' ', $value);
        $value = trim($value);

        // Collapse whitespace
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }
}
