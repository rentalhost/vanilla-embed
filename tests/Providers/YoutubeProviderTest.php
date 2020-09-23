<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Providers\YoutubeProvider;

class YoutubeProviderTest
    extends TestCase
{
    public function dataProviderIsUrlCompatible(): array
    {
        return [
            // Valid URLs.
            [ 'm.youtube.com/watch?v=kJQP7kiw5Fk', true ],
            [ 'youtube.com/watch?v=kJQP7kiw5Fk', true ],
            [ 'youtube.com/watch/?v=kJQP7kiw5Fk', true ],
            [ 'youtube.com/embed/kJQP7kiw5Fk', true ],
            [ 'youtu.be/kJQP7kiw5Fk', true ],

            // Invalid URLs.
            [ 'youtube.com/watch', false ],
            [ 'youtube.com/watch/?vi=kJQP7kiw5Fk', false ],
            [ 'youtube.com/kJQP7kiw5Fk', false ],
            [ 'youtube.be/kJQP7kiw5Fk', false ],

            // Invalid ID URLs.
            [ 'youtu.be/kJQP7kiw5FkInvalid', false ],

            // Invalid Provider URLs.
            [ 'youtube.com.br/kJQP7kiw5Fk', false ],
        ];
    }

    /** @dataProvider dataProviderIsUrlCompatible */
    public function testIsUrlCompatible(string $url, bool $isValid): void
    {
        static::assertSame($isValid, YoutubeProvider::isUrlCompatible($url));
    }
}
