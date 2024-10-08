<?php

declare(strict_types=1);

namespace Rentalhost\Vanilla\Embed\Tests\Support;

use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Support\UrlSupport;

class UrlSupportTest
    extends TestCase
{
    public static function testError404(): void
    {
        $postmanCachePath = getcwd() . '/tests/.cache/' . UrlSupport::getCacheKey('https://postman-echo.com/status/404');

        if (is_file($postmanCachePath)) {
            unlink($postmanCachePath);
        }

        static::assertNull(UrlSupport::getContents('https://postman-echo.com/status/404'));
    }

    public static function testError429(): void
    {
        static::assertNull(UrlSupport::getContents('https://postman-echo.com/status/429'));
    }

    public static function testExtractMetas(): void
    {
        $postmanCachePath = getcwd() . '/tests/.cache/' . UrlSupport::getCacheKey('https://postman-echo.com/status/200');

        if (is_file($postmanCachePath)) {
            unlink($postmanCachePath);
        }

        static::assertSame("{\n  \"status\": 200\n}", UrlSupport::getContents('https://postman-echo.com/status/200'));
    }

    public function testError402(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(402);

        UrlSupport::getContents('https://postman-echo.com/status/402');
    }
}
