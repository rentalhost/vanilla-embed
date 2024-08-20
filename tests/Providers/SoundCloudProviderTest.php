<?php

declare(strict_types=1);

namespace Rentalhost\Vanilla\Embed\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\Providers\EmbedData\SoundCloudEmbedData;
use Rentalhost\Vanilla\Embed\Providers\SoundCloudProvider;

class SoundCloudProviderTest
    extends TestCase
{
    public static function dataProviderIsUrlCompatible(): array
    {
        return [
            // Valid URLs.
            [ 'soundcloud.com/david-rodrigues-277280782/impact-moderato', true ],
            [ 'soundcloud.com/david-rodrigues-277280782/impact-moderato/s-MjcQ5BtcRPp', true ],
            [ 'w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/898937494%3Fsecret_token%3Ds-MjcQ5BtcRPp', true ],
            [ 'w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/898962922', true ],

            // Invalid URLs.
            [ 'soundcloud.com/david-rodrigues-277280782', false ],
            [ 'w.soundcloud.com/player', false ],

            // Invalid unlisted URLs (no secret token).
            [ 'w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/898937494', false ],

            // Invalid Provider URLs.
            [ 'invalid.soundcloud.com/david-rodrigues-277280782/impact-moderato', false ],
        ];
    }

    /** @dataProvider dataProviderIsUrlCompatible */
    public static function testIsUrlCompatible(string $url, bool $isValid): void
    {
        static::assertSame($isValid, SoundCloudProvider::isUrlCompatible($url));
    }

    public function testEmbedDataGetThumbnail(): void
    {
        /** @var SoundCloudEmbedData $embedData */
        $embedData          = Embed::create()->fromUrl('https://soundcloud.com/david-rodrigues-277280782/impact-moderato/s-MjcQ5BtcRPp');
        $embedDataThumbnail = $embedData->getThumbnail();

        static::assertSame('https://i1.sndcdn.com/avatars-000822826894-i1lc70-t500x500.jpg', $embedDataThumbnail->url);
        static::assertSame(500, $embedDataThumbnail->width);
        static::assertSame(500, $embedDataThumbnail->height);

        $embedDataThumbnailTiny = $embedData->getThumbnail('tiny');

        static::assertSame('https://i1.sndcdn.com/avatars-000822826894-i1lc70-tiny.jpg', $embedDataThumbnailTiny->url);
        static::assertSame(18, $embedDataThumbnailTiny->width);
        static::assertSame(18, $embedDataThumbnailTiny->height);
    }
}
