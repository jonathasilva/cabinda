<?php

declare(strict_types=1);

namespace Astroinfo\App\Parser;

use Astroinfo\App\Http\AstroseekLogin;
use RuntimeException;

final class AstroSeekHtmlFetcher
{
    public function fetch(string $url): string
    {
        $login = AstroseekLogin::$instance;
        if (!$login)
        {
            throw new RuntimeException("AstroseekLogin instance not found. Ensure it is instantiated before using AstroSeekHtmlFetcher.");
        }

        $response = $login->fetch($url);

        return $response;
    }
}
