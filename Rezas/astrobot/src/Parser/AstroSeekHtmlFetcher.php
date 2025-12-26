<?php

declare(strict_types=1);

namespace Astroinfo\App\Parser;

use RuntimeException;

final class AstroSeekHtmlFetcher
{
    public function fetch(string $url): string
    {
        $ch = curl_init($url);
        if ($ch === false)
        {
            throw new RuntimeException('Failed to init cURL.');
        }

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 5,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 25,

                // Important headers to look like a browser
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
                CURLOPT_HTTPHEADER =>
                [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: pt-BR,pt;q=0.9,en;q=0.8',
                    'Cache-Control: no-cache',
                    'Pragma: no-cache',
                ],

                // SSL (normalmente deixa true; se seu Windows estiver sem CA, isso pode falhar)
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]
        );

        $html = curl_exec($ch);
        $errNo = curl_errno($ch);
        $errMsg = curl_error($ch);

        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($html === false)
        {
            throw new RuntimeException('cURL error (' . $errNo . '): ' . $errMsg);
        }

        if ($status < 200 || $status >= 300)
        {
            throw new RuntimeException('HTTP status ' . $status . ' when fetching Astro-Seek.');
        }

        $html = (string)$html;

        if (trim($html) === '')
        {
            throw new RuntimeException('Empty HTML returned from Astro-Seek.');
        }

        return $html;
    }
}
