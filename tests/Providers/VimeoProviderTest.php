<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\Exceptions\InvalidClientKeyException;
use Rentalhost\Vanilla\Embed\Providers\EmbedData\VimeoEmbedData;
use Rentalhost\Vanilla\Embed\Providers\VimeoProvider;
use Rentalhost\Vanilla\Embed\Support\UrlSupport;

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
            [ 'invalid.vimeo.com/29950141', false ],
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

    public function testInvalidJsonResponse(): void
    {
        $cachePath = getcwd() . '/tests/.cache/' . UrlSupport::getCacheKey('https://player.vimeo.com/video/124');

        file_put_contents($cachePath, /** @lang text */ '<script> var config = invalid; if (!config.request) {} </script>');

        $embedData = Embed::create()->fromUrl('https://player.vimeo.com/video/124');

        static::assertFalse($embedData->found);
        static::assertSame('124', $embedData->id);
    }

    public function testInvalidResponse(): void
    {
        $cachePath = getcwd() . '/tests/.cache/' . UrlSupport::getCacheKey('https://player.vimeo.com/video/123');

        file_put_contents($cachePath, 'invalid-response');

        $embedData = Embed::create()->fromUrl('https://player.vimeo.com/video/123');

        static::assertFalse($embedData->found);
        static::assertSame('123', $embedData->id);
    }

    /** @dataProvider dataProviderIsUrlCompatible */
    public function testIsUrlCompatible(string $url, bool $isValid): void
    {
        static::assertSame($isValid, VimeoProvider::isUrlCompatible($url));
    }

    public function testWithInvalidVimeoKey(): void
    {
        $this->expectException(InvalidClientKeyException::class);
        $this->expectErrorMessage('No user credentials were provided.');

        Embed::create([ 'vimeo.accessToken' => 'invalidAccessToken' ])
            ->fromUrl('https://player.vimeo.com/video/460466078');
    }

    public function testWithVimeoKey(): void
    {
        $vimeoAccessToken = getenv('VIMEO_ACCESS_TOKEN');

        if (!$vimeoAccessToken) {
            static::markTestSkipped('VIMEO_ACCESS_TOKEN is not available as environment variable');
        }

        $embedData = Embed::create([ 'vimeo.accessToken' => $vimeoAccessToken ])
            ->fromUrl('https://vimeo.com/460466078');

        static::assertTrue($embedData->found);
        static::assertStringContainsString('Gestão', $embedData->title);
        static::assertStringContainsString('Aula', $embedData->description);
        static::assertIsArray($embedData->tags);

        $embedDataThumbnail = $embedData->getThumbnail();

        static::assertStringEndsWith('/961795826_100x75.jpg', $embedDataThumbnail->url);

        $embedData = Embed::create([ 'vimeo.accessToken' => $vimeoAccessToken ])
            ->fromUrl('https://vimeo.com/344997253/ab1b6f2867');

        static::assertTrue($embedData->found);
        static::assertSame('CAP Roundtable 2019.06.26', $embedData->title);
        static::assertStringContainsString('CAP Roundtable 06/26/2019', $embedData->description);
        static::assertContains('CAP Roundtable', $embedData->tags);
        static::assertSame('https://vimeo.com/344997253/ab1b6f2867', $embedData->url);

        $embedDataThumbnail = $embedData->getThumbnail();

        static::assertStringEndsWith('/811379236_1280x656.jpg', $embedDataThumbnail->url);

        $embedData = Embed::create([ 'vimeo.accessToken' => $vimeoAccessToken ])
            ->fromUrl('https://player.vimeo.com/video/1');

        static::assertFalse($embedData->found);
        static::assertNull($embedData->title);
        static::assertSame('https://vimeo.com/1', $embedData->url);
    }
}
