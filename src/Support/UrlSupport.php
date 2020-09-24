<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Support;

use GuzzleHttp\Client as GuzzleClient;

class UrlSupport
{
    private static function getContentsUncached(string $url, ?array $querystring): ?string
    {
        return (new GuzzleClient)->get($url, [ 'query' => $querystring ?? [] ])->getBody()->getContents();
    }

    public static function getContents(string $url, ?array $querystring = null): ?string
    {
        $urlCacheEnabled = (bool) getenv('PHPUNIT_URL_CACHE_ENABLED');

        if ($urlCacheEnabled) {
            $urlHostname  = parse_url($url, PHP_URL_HOST);
            $urlCacheKey  = $urlHostname . '-' . sha1($url . '@' . json_encode($querystring, JSON_THROW_ON_ERROR));
            $urlCachePath = getcwd() . '/tests/.cache/' . $urlCacheKey;

            if (is_file($urlCachePath)) {
                return file_get_contents($urlCachePath);
            }
        }

        $contentsUncached = self::getContentsUncached($url, $querystring);

        if ($urlCacheEnabled) {
            file_put_contents($urlCachePath, $contentsUncached);
        }

        return $contentsUncached;
    }
}
