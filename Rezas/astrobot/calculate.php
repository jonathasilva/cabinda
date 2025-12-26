<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require_once __DIR__ . '/vendor/autoload.php';

use Astroinfo\App\ChartFormRequest;
use Astroinfo\App\Parser\PlanetPositionsParser;
use Astroinfo\App\URL\TraditionalChartParams;
use Astroinfo\App\Parser\TraditionalChartParser;

header('Content-Type: text/html; charset=utf-8');

$form = new ChartFormRequest();

if (!$form->isValid())
{
    http_response_code(422);

    echo "Erros:\n";
    foreach ($form->errors() as $err)
    {
        echo "- " . $err . "\n";
    }

    exit;
}
$params = new TraditionalChartParams();
$parser = new TraditionalChartParser();

try
{
    $blocks = $parser->parseFromParams($params);
    $planetparser = new PlanetPositionsParser();

    $positions = $planetparser->parseFromVypocetPlanetHtml($blocks[0]['html']);

    dd($positions);
}
catch (Throwable $e)
{
    http_response_code(500);
    echo "Parser error: " . $e->getMessage();
}
