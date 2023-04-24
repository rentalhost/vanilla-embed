<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Support;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use Rentalhost\Vanilla\Embed\Exceptions\InvalidClientKeyException;

class UrlSupport
{
    private static function getContentsUncached(string $url, array|null $querystring, array|null $headers = null): string|null
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $urlQuerystring);

        try {
            return (new GuzzleClient())->get($url, [
                'headers' => $headers ?? [],
                'query'   => [ ...$urlQuerystring, ...$querystring ?? [] ],
            ])->getBody()->getContents();
        }
        catch (ClientException $exception) {
            $exceptionCode = $exception->getCode();

            if (in_array($exceptionCode, [ 404, 403, 429 ], true)) {
                return null;
            }

            if (in_array($exceptionCode, [ 400, 401 ], true)) {
                $exceptionResponse = json_decode($exception->getResponse()->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                throw new InvalidClientKeyException(
                    $exceptionResponse['error']['message'] ?? $exceptionResponse['developer_message'],
                    $exceptionCode,
                    $exception
                );
            }

            throw $exception;
        }
    }

    public static function getCacheKey(string $url, array|null $querystring = null, array|null $headers = null): string
    {
        $urlHostname = parse_url($url, PHP_URL_HOST);

        return $urlHostname . '-' . sha1($url . '@' . json_encode($querystring, JSON_THROW_ON_ERROR) . '@' . json_encode($headers, JSON_THROW_ON_ERROR));
    }

    public static function getContents(string $url, array|null $querystring = null, array|null $headers = null): string|null
    {
        $urlCacheEnabled = (bool) getenv('PHPUNIT_URL_CACHE_ENABLED');

        if ($urlCacheEnabled) {
            $urlCacheKey  = self::getCacheKey($url, $querystring, $headers);
            $urlCachePath = getcwd() . '/tests/.cache/' . $urlCacheKey;

            if (is_file($urlCachePath)) {
                return file_get_contents($urlCachePath) ?: null;
            }
        }

        $contentsUncached = self::getContentsUncached($url, $querystring, $headers);

        if ($urlCacheEnabled) {
            file_put_contents($urlCachePath, $contentsUncached);
        }

        return $contentsUncached ?: null;
    }
}
