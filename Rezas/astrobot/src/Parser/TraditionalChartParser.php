<?php

declare(strict_types=1);

namespace Astroinfo\App\Parser;

use Astroinfo\App\Parser\AstroSeekHtmlFetcher;
use Astroinfo\App\Traditional\AntisciaEntry;
use Astroinfo\App\Traditional\AntisciaPart;
use Astroinfo\App\Traditional\DodecatemoriaEntry;
use Astroinfo\App\Traditional\DodecatemoriaPart;
use Astroinfo\App\Traditional\PlanetaryHours;
use Astroinfo\App\URL\TraditionalChartParams;
use DiDom\Document;
use DiDom\Element;
use RuntimeException;

final class TraditionalChartParser
{
    private AstroSeekHtmlFetcher $Fetcher;

    public function __construct(?AstroSeekHtmlFetcher $fetcher = null)
    {
        $this->Fetcher = $fetcher ?? new AstroSeekHtmlFetcher();
    }

    /**
     * @return array<int, array{index:int, html:string, text:string}>
     */
    public function parseFromParams(TraditionalChartParams $params): array
    {
        $url = $params->toUrl();
        $html = $this->Fetcher->fetch($url);

        $document = new Document($html);

        $nodes = $document->find('.vypocet-planet');

        if (count($nodes) < 5)
        {
            $snippet = mb_substr(trim($document->text()), 0, 240);

            throw new RuntimeException(
                'Expected at least 5 elements with class "vypocet-planet", got ' . count($nodes) .
                    '. HTML snippet: ' . $snippet
            );
        }

        $result = [];

        for ($i = 1; $i <= 4; $i++)
        {
            $result[] =
                [
                    'index' => $i,
                    'html'  => $nodes[$i]->html(),
                    'text'  => trim($nodes[$i]->text()),
                ];
        }

        return $result;
    }

    public function parsePlanetaryHoursFromParams(TraditionalChartParams $params): PlanetaryHours
    {
        $document = $this->fetchDocument($params);

        $items = $document->find('.detail-rozbor-items');

        if (\count($items) < 3)
        {
            throw new RuntimeException('Expected at least 3 elements with class "detail-rozbor-items".');
        }

        $block = $items[2];

        $tables = $block->find('table');

        if (\count($tables) < 1)
        {
            throw new RuntimeException('No <table> found inside detail-rozbor-items[2] for Planetary Hours.');
        }

        $phTable = $tables[0];

        $imgs = $phTable->find('img');

        if (\count($imgs) < 2)
        {
            throw new RuntimeException('Expected at least 2 <img> tags in Planetary Hours table for Day/Hour planets.');
        }

        $dayPlanet = $this->planetFromImgSrc((string)($imgs[0]->getAttribute('src') ?? ''));
        $hourPlanet = $this->planetFromImgSrc((string)($imgs[1]->getAttribute('src') ?? ''));

        $lastHourPlanet = null;
        $nextHourPlanet = null;
        $lastHourOffset = null;
        $nextHourOffset = null;

        foreach ($imgs as $img)
        {
            $src = (string)$img->getAttribute('src');
            $p = $this->planetFromImgSrc($src);

            if ($p === '')
            {
                continue;
            }

            $parentText = $this->normalizeText($img->parent()->text());

            if (stripos($parentText, 'Last Hr') !== false)
            {
                $lastHourPlanet = $p;
            }

            if (stripos($parentText, 'Next Hr') !== false)
            {
                $nextHourPlanet = $p;
            }
        }

        $infos = $phTable->find('.form-info');

        foreach ($infos as $info)
        {
            $t = $this->normalizeText($info->text());

            if (strpos($t, '-') !== false && $lastHourOffset === null)
            {
                $lastHourOffset = $t;
                continue;
            }

            if (strpos($t, '+') !== false && $nextHourOffset === null)
            {
                $nextHourOffset = $t;
            }
        }

        $hourLabel = null;
        $text = $this->normalizeText($phTable->text());

        if (preg_match('/\b(\d+)(st|nd|rd|th)\s+hour\s+of\s+Day\b/i', $text, $m) === 1)
        {
            $hourLabel = $m[0];
        }

        if ($dayPlanet === '' || $hourPlanet === '')
        {
            throw new RuntimeException('Could not resolve Day/Hour planet from Planetary Hours table images.');
        }

        return new PlanetaryHours(
            $dayPlanet,
            $hourPlanet,
            $lastHourPlanet,
            $lastHourOffset,
            $nextHourPlanet,
            $nextHourOffset,
            $hourLabel
        );
    }

    /**
     * @return array{dodecatemoria:DodecatemoriaEntry[], antiscia:AntisciaEntry[]}
     */
    public function parseDodecatemoriaAndAntisciaFromParams(TraditionalChartParams $params): array
    {
        $document = $this->fetchDocument($params);

        $items = $document->find('.detail-rozbor-items');

        if (\count($items) < 4)
        {
            throw new RuntimeException('Expected at least 4 elements with class "detail-rozbor-items".');
        }

        $block = $items[3];

        $tables = $block->find('table');

        if (\count($tables) < 2)
        {
            throw new RuntimeException('Expected at least 2 <table> inside detail-rozbor-items[3] (Dodecatemoria + Antiscia).');
        }

        $dodecTable = $tables[0];
        $antisciaTable = $tables[1];

        $dodecEntries = $this->parseDodecatemoriaTable($dodecTable);
        $antisciaEntries = $this->parseAntisciaTable($antisciaTable);

        return
            [
                'dodecatemoria' => $dodecEntries,
                'antiscia' => $antisciaEntries,
            ];
    }

    private function fetchDocument(TraditionalChartParams $params): Document
    {
        $url = $params->toUrl();
        $html = $this->Fetcher->fetch($url);

        return new Document($html);
    }

    /**
     * @return DodecatemoriaEntry[]
     */
    private function parseDodecatemoriaTable(Element $table): array
    {
        $trs = $table->find('tr');

        if (\count($trs) <= 1)
        {
            return [];
        }

        $result = [];

        for ($i = 1; $i < \count($trs); $i++)
        {
            $tr = $trs[$i];
            $tds = $tr->find('td');

            if (\count($tds) < 3)
            {
                continue;
            }

            // Dodecatemoria point is text ("ASC", "MC", "Sun"...), so td[0]->text() is OK here.
            $point = $this->normalizeText($tds[0]->text());

            if ($point === '' || stripos($point, 'Open:') !== false)
            {
                continue;
            }

            $twelfth = $this->parseDodecatemoriaPartCell($tds[1]);
            $ninth = $this->parseDodecatemoriaPartCell($tds[2]);

            if ($twelfth === null || $ninth === null)
            {
                continue;
            }

            $result[] = new DodecatemoriaEntry($point, $twelfth, $ninth);
        }

        return $result;
    }

    private function parseDodecatemoriaPartCell(Element $td): ?DodecatemoriaPart
    {
        $parsed = $this->parseSignAndDegMinFromCell($td);

        if ($parsed === null)
        {
            return null;
        }

        return new DodecatemoriaPart($parsed['sign'], $parsed['deg'], $parsed['min']);
    }

    /**
     * Antiscia: td[0] may be ONLY an image (no text).
     *
     * @return AntisciaEntry[]
     */
    private function parseAntisciaTable(Element $table): array
    {
        $trs = $table->find('tr');

        if (\count($trs) <= 1)
        {
            return [];
        }

        $result = [];

        for ($i = 1; $i < \count($trs); $i++)
        {
            $tr = $trs[$i];
            $tds = $tr->find('td');

            if (\count($tds) < 3)
            {
                continue;
            }

            $point = $this->parsePointFromCell($tds[0]);

            if ($point === '' || stripos($point, 'Open:') !== false)
            {
                continue;
            }

            $anti = $this->parseSignAndDegMinFromCell($tds[1]);
            $contra = $this->parseSignAndDegMinFromCell($tds[2]);

            if ($anti === null || $contra === null)
            {
                continue;
            }

            $result[] = new AntisciaEntry(
                $point,
                new AntisciaPart($anti['sign'], $anti['deg'], $anti['min']),
                new AntisciaPart($contra['sign'], $contra['deg'], $contra['min'])
            );
        }

        return $result;
    }

    private function parsePointFromCell(Element $td): string
    {
        // Preferred: explicit text if present
        $text = $this->normalizeText($td->text());

        if ($text !== '')
        {
            // Sometimes text can be like "ASC" or "MC" etc.
            return $text;
        }

        // Fallback: parse from img src
        $img = $td->first('img');

        if ($img === null)
        {
            return '';
        }

        $src = (string)($img->getAttribute('src') ?? '');

        return $this->pointFromImgSrc($src);
    }

    private function pointFromImgSrc(string $src): string
    {
        $src = strtolower($src);

        // Houses / angles
        if (str_contains($src, 'planeta-najdise-oranzova-dum_1.png'))
        {
            return 'ASC';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-dum_10.png'))
        {
            return 'MC';
        }

        // Traditional planets + points (Astro-Seek orange icons)
        if (str_contains($src, 'planeta-najdise-oranzova-slunce.png'))
        {
            return 'Sun';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-luna.png'))
        {
            return 'Moon';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-merkur.png'))
        {
            return 'Mercury';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-venuse.png'))
        {
            return 'Venus';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-mars.png'))
        {
            return 'Mars';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-jupiter.png'))
        {
            return 'Jupiter';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-saturn.png'))
        {
            return 'Saturn';
        }

        // Modern planets
        if (str_contains($src, 'planeta-najdise-oranzova-uran.png'))
        {
            return 'Uranus';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-neptun.png'))
        {
            return 'Neptune';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-pluto.png'))
        {
            return 'Pluto';
        }

        // Points
        if (str_contains($src, 'planeta-najdise-oranzova-uzel.png'))
        {
            return 'Node';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-fortune.png'))
        {
            return 'Fortune';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-spirit.png'))
        {
            return 'Spirit';
        }

        if (str_contains($src, 'planeta-najdise-oranzova-syzygy.png'))
        {
            return 'Syzygy';
        }

        // Fallback: try the "outline" set you already support (used in Planetary Hours)
        $planet = $this->planetFromImgSrc($src);

        if ($planet !== '')
        {
            return $planet;
        }

        return '';
    }

    /**
     * @return array{sign:string, deg:int, min:int}|null
     */
    private function parseSignAndDegMinFromCell(Element $td): ?array
    {
        $img = $td->first('img');

        if ($img === null)
        {
            return null;
        }

        $src = (string)($img->getAttribute('src') ?? '');
        $alt = (string)($img->getAttribute('alt') ?? '');

        $sign = $this->signFromProfileSymbolImgSrc($src);

        if ($sign === '')
        {
            $sign = $this->signFromProfileSymbolAlt($alt);
        }

        if ($sign === '')
        {
            $sign = $this->signFromImgSrc($src);
        }

        if ($sign === '')
        {
            return null;
        }

        $text = $this->normalizeText($td->text());
        [$deg, $min] = $this->parseDegMin($text);

        return
            [
                'sign' => $sign,
                'deg' => $deg,
                'min' => $min,
            ];
    }

    private function signFromProfileSymbolImgSrc(string $src): string
    {
        $src = strtolower($src);

        $map =
            [
                'symbol_aries_' => 'Aries',
                'symbol_taurus_' => 'Taurus',
                'symbol_gemini_' => 'Gemini',
                'symbol_cancer_' => 'Cancer',
                'symbol_leo_' => 'Leo',
                'symbol_virgo_' => 'Virgo',
                'symbol_libra_' => 'Libra',
                'symbol_scorpio_' => 'Scorpio',
                'symbol_sagittarius_' => 'Sagittarius',
                'symbol_capricorn_' => 'Capricorn',
                'symbol_aquarius_' => 'Aquarius',
                'symbol_pisces_' => 'Pisces',
            ];

        foreach ($map as $needle => $sign)
        {
            if (str_contains($src, $needle))
            {
                return $sign;
            }
        }

        return '';
    }

    private function signFromProfileSymbolAlt(string $alt): string
    {
        $alt = trim($alt);

        if ($alt === '')
        {
            return '';
        }

        $alt = trim($alt, " ,\t\n\r\0\x0B");

        $allowed =
            [
                'Aries',
                'Taurus',
                'Gemini',
                'Cancer',
                'Leo',
                'Virgo',
                'Libra',
                'Scorpio',
                'Sagittarius',
                'Capricorn',
                'Aquarius',
                'Pisces',
            ];

        foreach ($allowed as $s)
        {
            if (strcasecmp($alt, $s) === 0)
            {
                return $s;
            }
        }

        return '';
    }

    /**
     * Returns [degree, minute]
     */
    private function parseDegMin(string $text): array
    {
        $text = str_replace(["’", "′", "´", "`"], ["'", "'", "'", "'"], $text);

        if (preg_match('/(\d+)\s*°\s*(\d+)\s*\'/u', $text, $m) === 1)
        {
            return [(int)$m[1], (int)$m[2]];
        }

        if (preg_match('/(\d+)\s*°/u', $text, $m) === 1)
        {
            return [(int)$m[1], 0];
        }

        return [0, 0];
    }

    private function planetFromImgSrc(string $src): string
    {
        $src = strtolower($src);

        if (str_contains($src, 'planeta-stredni-obrys-jupiter.png'))
        {
            return 'Jupiter';
        }

        if (str_contains($src, 'planeta-stredni-obrys-slunce.png'))
        {
            return 'Sun';
        }

        if (str_contains($src, 'planeta-stredni-obrys-mars.png'))
        {
            return 'Mars';
        }

        if (str_contains($src, 'planeta-stredni-obrys-venuse.png'))
        {
            return 'Venus';
        }

        if (str_contains($src, 'planeta-stredni-obrys-luna.png'))
        {
            return 'Moon';
        }

        if (str_contains($src, 'planeta-stredni-obrys-saturn.png'))
        {
            return 'Saturn';
        }

        if (str_contains($src, 'planeta-stredni-obrys-merkur.png'))
        {
            return 'Mercury';
        }

        return '';
    }

    private function signFromImgSrc(string $src): string
    {
        $src = strtolower($src);

        $map =
            [
                'horoskop-beran' => 'Aries',
                'horoskop-byk' => 'Taurus',
                'horoskop-bliz' => 'Gemini',
                'horoskop-rak' => 'Cancer',
                'horoskop-lev' => 'Leo',
                'horoskop-panna' => 'Virgo',
                'horoskop-vahy' => 'Libra',
                'horoskop-stir' => 'Scorpio',
                'horoskop-strel' => 'Sagittarius',
                'horoskop-kozoroh' => 'Capricorn',
                'horoskop-vodnar' => 'Aquarius',
                'horoskop-ryby' => 'Pisces',
            ];

        foreach ($map as $needle => $sign)
        {
            if (str_contains($src, $needle))
            {
                return $sign;
            }
        }

        return '';
    }

    private function normalizeText(string $value): string
    {
        $value = str_replace("\xc2\xa0", ' ', $value);
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return $value;
    }
}
