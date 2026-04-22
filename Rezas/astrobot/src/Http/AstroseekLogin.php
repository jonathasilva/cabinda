<?php

declare(strict_types=1);

namespace Astroinfo\App\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use RuntimeException;

final class AstroseekLogin
{
    public static ?AstroseekLogin $instance = null;
    private $username;
    private $password;

    private Client $client;
    private CookieJar $cookieJar;
    private bool $isLoggedIn = false;
    private int $allowedRedirects = 5;
    private int $timeout = 25;
    private int $connectionTimeout = 10;
    private bool $verifySSL = true;
    private string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36';

    public function __construct()
    {
        self::$instance = $this;

        $this->username = getenv('ASTROSEEK_USERNAME') ?: 'jonathahsilva';
        $this->password = getenv('ASTROSEEK_PASSWORD') ?: '1K$62*$lAt6!^1#';

        $this->cookieJar = new CookieJar();
        $this->client = new Client([
            'cookies' => $this->cookieJar,
            'allow_redirects' => ['max' => $this->allowedRedirects],
            'timeout' => $this->timeout,
            'connect_timeout' => $this->connectionTimeout,
            'verify' => $this->verifySSL,
            'headers' => [
                'User-Agent'      => $this->userAgent,
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language'  => 'en-US,en;q=0.6',
                'Accept-Encoding'  => 'gzip, deflate, br',
                'sec-ch-ua'       => '"Brave";v="147", "Not.A/Brand";v="8", "Chromium";v="147"',
                'sec-ch-ua-mobile'   => '?0',
                'sec-ch-ua-platform' => '"Windows"',
                'sec-gpc'         => '1',
                'sec-fetch-dest' => 'document',
                'sec-fetch-mode' => 'navigate',
                'sec-fetch-site' => 'same-origin',
                'sec-fetch-user' => '?1',
                'Referer'        => 'https://www.astro-seek.com/',
                'Origin'         => 'https://www.astro-seek.com',
                'Connection'      => 'keep-alive',
                'Upgrade-Insecure-Requests' => '1',
            ],
        ]);
    }
    public function login(): bool
    {
        try
        {
            // PASSO 1: "Aquecer" a sessão (GET)
            // Isso simula você abrindo a página de login antes de digitar os dados
            /* $this->client->get('https://www.astro-seek.com/login', [
                'headers' => [
                    
                ]
            ]); */

            $response = $this->client->post('https://www.astro-seek.com/login', [
                'form_params' => [
                    'no_back' => 'no',
                    'login'   => $this->username,
                    'heslo'   => $this->password,
                    'trvale_prihlaseni' => 'on',
                ],
                'headers' => [
                    'Referer'         => 'https://www.astro-seek.com/login',
                    'Content-Type'    => 'application/x-www-form-urlencoded',
                ],
            ]);

            $html = (string) $response->getBody();

            if (stripos($html, 'You have been logged in successfully!') !== false)
            {
                $this->isLoggedIn = true;
                return true;
            }
            if ($response->getStatusCode() === 200)
            {
                $this->isLoggedIn = true;
                return true;
            }
            else
                throw new \Exception('Unexpected response status: ' . $response->getStatusCode());
        }
        catch (\Exception $e)
        {
            $this->isLoggedIn = false;
            error_log('Login failed: ' . $e->getMessage());
            return false;
        }
    }

    public function fetch(string $url): string
    {
        $cookies = $this->cookieJar->toArray();
        foreach ($cookies as $cookie)
        {
            // Check if the domain matches or is a wildcard (.astro-seek.com)
            // If it's strictly 'www.astro-seek.com', it won't work for 'horoscopes.'
            error_log("Cookie: " . $cookie['Name'] . " | Domain: " . $cookie['Domain']);
        }

        $response = $this->client->get($url, [
            'headers' => [
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                'Accept-Encoding' => 'gzip, deflate, br, zstd', // Added zstd for modern browser signature
                'Referer'         => 'https://horoscopes.astro-seek.com/', // Match the subdomain
                'sec-fetch-dest'  => 'document',
                'sec-fetch-mode'  => 'navigate',
                'sec-fetch-site'  => 'none', // Browser sent 'none' in your log
                'sec-fetch-user'  => '?1',
                'Priority'        => 'u=0, i', // Critical for some anti-bot checks
            ],
            'decode_content' => true, // Ensure Guzzle handles the compression automatically
        ]);

        $status = $response->getStatusCode();

        if ($status < 200 || $status >= 300)
        {
            throw new RuntimeException('HTTP status ' . $status . ' when fetching Astro-Seek.');
        }

        $html = (string) $response->getBody();

        if (trim($html) === '')
        {
            throw new RuntimeException('Empty HTML returned from Astro-Seek.');
        }

        return $html;
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    /**
     * Clear all cookies (logout / reset session).
     */
    public function clearCookies(): void
    {
        $this->cookieJar->clear();
        $this->isLoggedIn = false;
    }
}
