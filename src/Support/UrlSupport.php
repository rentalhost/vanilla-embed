<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Support;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Rentalhost\Vanilla\Embed\Exceptions\InvalidClientKeyException;

class UrlSupport
{
    private static function getContentsUncached(string $url, ?array $querystring): ?string
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $urlQuerystring);

        try {
            return (new GuzzleClient)->get($url, [ 'query' => array_merge($urlQuerystring, $querystring ?? []) ])->getBody()->getContents();
        }
        catch (ClientException $exception) {
            if (in_array($exception->getCode(), [ 404, 403, 429 ], true)) {
                return null;
            }

            if ($exception->getCode() === 400) {
                $exceptionResponse = json_decode($exception->getResponse()->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                throw new InvalidClientKeyException($exceptionResponse['error']['message'], 400, $exception);
            }

            throw $exception;
        }
    }

    public static function getContents(string $url, ?array $querystring = null): ?string
    {
        $urlCacheEnabled = (bool) getenv('PHPUNIT_URL_CACHE_ENABLED');

        if ($urlCacheEnabled) {
            $urlHostname  = parse_url($url, PHP_URL_HOST);
            $urlCacheKey  = $urlHostname . '-' . sha1($url . '@' . json_encode($querystring, JSON_THROW_ON_ERROR));
            $urlCachePath = getcwd() . '/tests/.cache/' . $urlCacheKey;

            if (is_file($urlCachePath)) {
                return file_get_contents($urlCachePath) ?: null;
            }
        }

        $contentsUncached = self::getContentsUncached($url, $querystring);

        if ($urlCacheEnabled) {
            file_put_contents($urlCachePath, $contentsUncached);
        }

        return $contentsUncached ?: null;
    }
}
