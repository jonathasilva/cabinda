<?php

declare(strict_types=1);

namespace Astroinfo\App\Parser;

use Astroinfo\App\Parser\AstroSeekHtmlFetcher;
use Astroinfo\App\URL\TraditionalChartParams;
use DiDom\Document;
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

        // Debug tip: if it still returns 0 nodes, inspect what HTML you got
        // file_put_contents(__DIR__ . '/../../debug_astroseek.html', $html);

        $document = new Document($html);

        $nodes = $document->find('.vypocet-planet');

        if (count($nodes) < 5)
        {
            // Helpful: detect if it looks like a block page
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
}
