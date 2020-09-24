<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\Providers\VimeoProvider;

class VimeoProviderTest
    extends TestCase
{
    public function dataProviderIsUrlCompatible(): array
    {
        return [
            // Valid URLs.
            [ 'vimeo.com/29950141', true ],
            [ 'player.vimeo.com/video/29950141', true ],
            [ 'vimeo.com/29950141/abc123', true ],

            // Invalid URLs.
            [ 'vimeo.com/29950141invalid', false ],

            // Invalid Provider URLs.
            [ 'invalid.vimeo.com/29950141', false ]
        ];
    }

    public function testEmbedDataGetThumbnail(): void
    {
        $embedDataThumbnail = Embed::create()->fromUrl('https://vimeo.com/29950141')->getThumbnail();

        static::assertStringEndsWith('/487882964_1280x720.jpg', $embedDataThumbnail->url);
        static::assertSame(1280, $embedDataThumbnail->width);
        static::assertSame(720, $embedDataThumbnail->height);
    }

    /** @dataProvider dataProviderIsUrlCompatible */
    public function testIsUrlCompatible(string $url, bool $isValid): void
    {
        static::assertSame($isValid, VimeoProvider::isUrlCompatible($url));
    }
}
