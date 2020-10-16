<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests\Support;

use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Support\UrlSupport;

class UrlSupportTest
    extends TestCase
{
    public function testError402(): void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(402);

        UrlSupport::getContents('https://postman-echo.com/status/402');
    }

    public function testError404(): void
    {
        $postmanCachePath = getcwd() . '/tests/.cache/postman-echo.com-cc3ede80dd94fb77c7540cc1ff58109b66969c19';

        if (is_file($postmanCachePath)) {
            unlink($postmanCachePath);
        }

        static::assertNull(UrlSupport::getContents('https://postman-echo.com/status/404'));
    }

    public function testExtractMetas(): void
    {
        $postmanCachePath = getcwd() . '/tests/.cache/postman-echo.com-9e120c81d39397f88f73b5444d6001ff08c895fc';

        if (is_file($postmanCachePath)) {
            unlink($postmanCachePath);
        }

        static::assertSame('{"status":200}', UrlSupport::getContents('https://postman-echo.com/status/200'));
    }
}
