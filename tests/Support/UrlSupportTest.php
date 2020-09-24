<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests\Support;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Support\UrlSupport;

class UrlSupportTest
    extends TestCase
{
    public function testExtractMetas(): void
    {
        $postmanCachePath = getcwd() . '/tests/.cache/postman-echo.com-9e120c81d39397f88f73b5444d6001ff08c895fc';

        if (is_file($postmanCachePath)) {
            unlink($postmanCachePath);
        }

        static::assertSame('{"status":200}', UrlSupport::getContents('https://postman-echo.com/status/200'));
    }
}
