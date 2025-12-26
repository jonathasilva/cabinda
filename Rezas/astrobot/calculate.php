<?php

declare(strict_types=1);
require_once __DIR__ . './vendor/autoload.php';

use Astroinfo\App\AstroSeek\TraditionalChartParams;
use Astroinfo\App\ChartFormRequest;

header('Content-Type: text/plain; charset=utf-8');

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
