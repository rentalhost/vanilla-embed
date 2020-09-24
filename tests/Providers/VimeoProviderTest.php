<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\Providers\EmbedData\VimeoEmbedData;
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
        /** @var VimeoEmbedData $embedData */
        $embedData          = Embed::create()->fromUrl('https://vimeo.com/344997253/ab1b6f2867');
        $embedDataThumbnail = $embedData->getThumbnail();

        static::assertStringEndsWith('/811379236_1280x656.jpg', $embedDataThumbnail->url);
        static::assertSame(1280, $embedDataThumbnail->width);
        static::assertSame(656, $embedDataThumbnail->height);
        static::assertSame('ab1b6f2867', $embedData->idKey);

        $embedDataThumbnailSized = $embedData->getThumbnailSized(640, 480);

        static::assertStringEndsWith('/811379236_640x480.jpg', $embedDataThumbnailSized->url);
        static::assertSame(640, $embedDataThumbnailSized->width);
        static::assertSame(480, $embedDataThumbnailSized->height);

        $embedDataThumbnailSizedWithoutHeight = $embedData->getThumbnailSized(640);

        static::assertStringEndsWith('/811379236_640.jpg', $embedDataThumbnailSizedWithoutHeight->url);
        static::assertSame(640, $embedDataThumbnailSizedWithoutHeight->width);
        static::assertNull($embedDataThumbnailSizedWithoutHeight->height);
    }

    /** @dataProvider dataProviderIsUrlCompatible */
    public function testIsUrlCompatible(string $url, bool $isValid): void
    {
        static::assertSame($isValid, VimeoProvider::isUrlCompatible($url));
    }
}
