<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require_once __DIR__ . '/vendor/autoload.php';

use Astroinfo\App\Aspects\AspectCalculator;
use Astroinfo\App\ChartFormRequest;
use Astroinfo\App\Http\AstroseekLogin;
use Astroinfo\App\ImportantDegrees;
use Astroinfo\App\Parser\ElementsParser;
use Astroinfo\App\Parser\HousePositionsParser;
use Astroinfo\App\Parser\PlanetPositionsParser;
use Astroinfo\App\URL\TraditionalChartParams;
use Astroinfo\App\Parser\TraditionalChartParser;
use Astroinfo\App\PlanetPosition;
use Astroinfo\App\Log\ErrorCollector;

// Initialize error handling
ErrorCollector::initErrorHandling();

// Determine the output format from the query string (defaults to "md")
$outputFormat = strtolower(trim($_GET['output_format'] ?? 'md'));

if ($outputFormat === 'json')
{
    header('Content-Type: application/json; charset=utf-8');
}
else
{
    header('Content-Type: text/markdown; charset=utf-8');
}

$form = new ChartFormRequest();

if (!$form->isValid())
{
    http_response_code(422);

    if ($outputFormat === 'json')
    {
        echo json_encode(['errors' => $form->errors()], JSON_UNESCAPED_UNICODE);
    }
    else
    {
        echo "Erros:\n";
        foreach ($form->errors() as $err)
        {
            echo "- " . $err . "\n";
        }
    }

    exit;
}

$login = new AstroseekLogin();
$params = new TraditionalChartParams($form);
$parser = new TraditionalChartParser();

try
{
    if (!$login->login())
    {
        throw new RuntimeException("Login failed. Please check your credentials and try again.", 500);
    }

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

    // Apply combustion/cazimi/under-beams dignities based on Sun
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

    // =========================================================================
    // JSON OUTPUT — compact (no pretty-print)
    // =========================================================================
    if ($outputFormat === 'json')
    {
        echo json_encode([
            'positions'                => $positions,
            'houses'                   => $houses,
            'elements'                 => $elements,
            'aspects'                  => $aspects,
            'planetaryHours'           => $planetaryHours,
            'extra'                    => $extra,
            'importantDegreeInformation' => $importantDegreeInformation,
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // =========================================================================
    // MARKDOWN OUTPUT
    // =========================================================================

    // Convert everything to associative array for easier Markdown iteration
    $data = json_decode(json_encode([
        'positions'                => $positions,
        'houses'                   => $houses,
        'aspects'                  => $aspects,
        'planetaryHours'           => $planetaryHours,
        'extra'                    => $extra,
        'importantDegreeInformation' => $importantDegreeInformation,
    ]), true);

    $md = "### ⏱️ Hora Planetária\n";
    $dayPlanet = $data['planetaryHours']['DayPlanet'] ?? '-';
    $hourPlanet = $data['planetaryHours']['HourPlanet'] ?? '-';
    $md .= "* **Dia:** {$dayPlanet}\n";
    $md .= "* **Hora:** {$hourPlanet}\n\n";
    $md .= "---\n\n";

    $md .= "### 🏠 Casas Astrológicas (Cúspides)\n";
    $md .= "| Casa | Ângulo | Signo | Posição |\n";
    $md .= "| :--- | :--- | :--- | :--- |\n";
    foreach ($data['houses'] as $h)
    {
        $angle = $h['AngleLabel'] ?? '-';
        $houseNum = in_array($h['House'], [1, 4, 7, 10]) ? "**{$h['House']}**" : $h['House'];
        $angleStr = $angle !== '-' ? "**{$angle}**" : '-';
        $md .= "| {$houseNum} | {$angleStr} | {$h['Sign']} | {$h['Position']} |\n";
    }
    $md .= "\n---\n\n";

    $md .= "### 🪐 Posições Planetárias\n";
    $md .= "| Planeta | Signo | Posição | Casa | Mov. | Velocidade | Dignidades / Condição |\n";
    $md .= "| :--- | :--- | :--- | :--- | :--- | :--- | :--- |\n";

    // Traditional planets + Node filter
    $tradPlanets = ['Sun', 'Moon', 'Mercury', 'Venus', 'Mars', 'Jupiter', 'Saturn', 'Node'];

    foreach ($data['positions'] as $p)
    {
        if (!in_array($p['Planet'], $tradPlanets)) continue;

        $digStr = empty($p['Dignities']) ? '-' : implode(', ', $p['Dignities']);
        $md .= "| **{$p['Planet']}** | {$p['Sign']} | {$p['Position']} | {$p['House']} | {$p['Motion']} | {$p['Speed']} | *{$digStr}* |\n";
    }
    $md .= "\n---\n\n";

    $md .= "### 📐 Aspectos Principais\n";
    $md .= "| Planeta A | Aspecto | Planeta B | Fase | Estado |\n";
    $md .= "| :--- | :--- | :--- | :--- | :--- |\n";

    $allowedPoints = array_merge($tradPlanets, ['ASC', 'MC', 'IC', 'DESC']);

    foreach ($data['aspects'] as $a)
    {
        // Skip aspects involving modern planets
        if (!in_array($a['A'], $allowedPoints) || !in_array($a['B'], $allowedPoints)) continue;

        $fase = $a['Phase'] === 'Applying' ? "**Aplicando**" : ($a['Phase'] === 'Separating' ? "Separando" : $a['Phase']);
        $md .= "| {$a['A']} | {$a['Aspect']} | {$a['B']} | {$fase} | {$a['State']} |\n";
    }
    $md .= "\n---\n\n";

    $md .= "### ⚠️ Graus Importantes & Condições Especiais\n";
    if (!empty($data['importantDegreeInformation']))
    {
        foreach ($data['importantDegreeInformation'] as $info)
        {
            // Clean up whitespace for a tidy bullet point
            $cleanInfo = trim(preg_replace('/\s+/', ' ', $info));
            $md .= "* {$cleanInfo}\n";
        }
    }
    else
    {
        $md .= "* Nenhuma condição especial detectada nos graus.\n";
    }
    $md .= "\n---\n\n";

    $md .= "### 🪞 Pontos Ocultos (Dodecatemoria e Antíscia)\n";
    $md .= "| Ponto | Dodecatemoria | Antíscia | Contra-Antíscia |\n";
    $md .= "| :--- | :--- | :--- | :--- |\n";

    // Map antiscia for easier lookup
    $antisciaMap = [];
    foreach ($data['extra']['antiscia'] as $ant)
    {
        $antisciaMap[$ant['Point']] = $ant;
    }

    foreach ($data['extra']['dodecatemoria'] as $dod)
    {
        $pt = $dod['Point'];
        if (!in_array($pt, $allowedPoints) && !in_array($pt, ['Fortune', 'Spirit', 'Syzygy'])) continue;

        $dodStr = "{$dod['TwelfthPart']['Degree']}º {$dod['TwelfthPart']['Minute']}' {$dod['TwelfthPart']['Sign']}";

        $antStr = '-';
        $contraStr = '-';
        if (isset($antisciaMap[$pt]))
        {
            $a = $antisciaMap[$pt]['Antiscia'];
            $c = $antisciaMap[$pt]['ContraAntiscia'];
            $antStr = "{$a['Degree']}º {$a['Minute']}' {$a['Sign']}";
            $contraStr = "{$c['Degree']}º {$c['Minute']}' {$c['Sign']}";
        }

        $md .= "| **{$pt}** | {$dodStr} | {$antStr} | {$contraStr} |\n";
    }

    // Output the final Markdown
    echo $md;
}
catch (Throwable $e)
{
    $code = $e->getCode() == 0 ? 500 : $e->getCode();
    http_response_code($code);

    if ($outputFormat === 'json')
    {
        $errors = json_decode(ErrorCollector::getLogsAsJson(), true);
        echo json_encode(['error' => $e->getMessage(), 'logs' => $errors], JSON_UNESCAPED_UNICODE);
    }
    else
    {
        echo "Ocorreu um erro durante o processamento:\n\n";
        echo "Parser error: " . $e->getMessage();
        $logs = json_decode(ErrorCollector::getLogsAsJson(), true);
        if (!empty($logs))
        {
            echo "\n\nLogs de Erro:\n";
            foreach ($logs as $log)
            {
                $level = strtoupper($log['level_name'] ?? 'LOG');
                $message = $log['message'] ?? '';
                echo "- [{$level}] {$message}\n";
            }
        }
    }
}
