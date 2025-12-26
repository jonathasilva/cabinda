<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require_once __DIR__ . '/vendor/autoload.php';

use Astroinfo\App\Aspects\AspectCalculator;
use Astroinfo\App\ChartFormRequest;
use Astroinfo\App\ImportantDegrees;
use Astroinfo\App\Parser\ElementsParser;
use Astroinfo\App\Parser\HousePositionsParser;
use Astroinfo\App\Parser\PlanetPositionsParser;
use Astroinfo\App\URL\TraditionalChartParams;
use Astroinfo\App\Parser\TraditionalChartParser;
use Astroinfo\App\PlanetPosition;

//header('Content-Type: text/html; charset=utf-8');
header('Content-Type: application/json; charset=utf-8');

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

    $planetshtml = $blocks[0]['html'];
    $housesHtml = $blocks[1]['html'] . $blocks[2]['html'];
    $elementsHtml = $blocks[3]['html'];

    $planetparser = new PlanetPositionsParser();
    $houseParser = new HousePositionsParser();
    $elementsParser = new ElementsParser();

    $positions = $planetparser->parseFromVypocetPlanetHtml($planetshtml);
    $houses = $houseParser->parseFromHousesHtml($housesHtml);
    $elements = $elementsParser->parseFromElementsHtml($elementsHtml);

    // Comments always in English: apply combustion/cazimi/under-beams dignities based on Sun
    $sun = null;
    foreach ($positions as $p)
    {
        if ($p instanceof PlanetPosition && $p->Planet === 'Sun')
        {
            $sun = $p;
            break;
        }
    }

    if ($sun instanceof PlanetPosition)
    {
        foreach ($positions as $p)
        {
            if (!$p instanceof PlanetPosition)
            {
                continue;
            }

            $p->applyCombustionFromSun($sun);
        }
    }

    $calculator = new AspectCalculator();
    $aspects = $calculator->calculate($positions, $houses);

    $planetaryHours = $parser->parsePlanetaryHoursFromParams($params);
    $extra = $parser->parseDodecatemoriaAndAntisciaFromParams($params);

    $importantDegrees = new ImportantDegrees();
    $importantDegreeInformation = $importantDegrees->computeAll($positions, $houses);

    echo json_encode([
        'positions' => $positions,
        'houses' => $houses,
        'elements' => $elements,
        'aspects' => $aspects,
        'planetaryHours' => $planetaryHours,
        'extra' => $extra,
        'importantDegreeInformation' => $importantDegreeInformation,
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
catch (Throwable $e)
{
    http_response_code(500);
    echo "Parser error: " . $e->getMessage();
}
