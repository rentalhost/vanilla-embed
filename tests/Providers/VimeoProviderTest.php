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
        $embedData          = Embed::create()->fromUrl('https://vimeo.com/273356233/83d37231b0');
        $embedDataThumbnail = $embedData->getThumbnail();

        static::assertStringEndsWith('/705132911_1280x720.jpg', $embedDataThumbnail->url);
        static::assertSame(1280, $embedDataThumbnail->width);
        static::assertSame(720, $embedDataThumbnail->height);
        static::assertSame('83d37231b0', $embedData->idKey);

        $embedDataThumbnailSized = $embedData->getThumbnailSized(640, 480);

        static::assertStringEndsWith('/705132911_640x480.jpg', $embedDataThumbnailSized->url);
        static::assertSame(640, $embedDataThumbnailSized->width);
        static::assertSame(480, $embedDataThumbnailSized->height);

        $embedDataThumbnailSizedWithoutHeight = $embedData->getThumbnailSized(640);

        static::assertStringEndsWith('/705132911_640.jpg', $embedDataThumbnailSizedWithoutHeight->url);
        static::assertSame(640, $embedDataThumbnailSizedWithoutHeight->width);
        static::assertNull($embedDataThumbnailSizedWithoutHeight->height);
    }

    public function testEmbedDataGetThumbnailNew(): void
    {
        /** @var VimeoEmbedData $embedData */
        $embedData          = Embed::create()->fromUrl('https://vimeo.com/783453158');
        $embedDataThumbnail = $embedData->getThumbnail();

        static::assertStringEndsWith('/1582080456-a2e326a6c36aa95042a2549ada80e8507061768e4aec00c887cd6531b5cfb499-d_1280x720.jpg', $embedDataThumbnail->url);
        static::assertSame(1280, $embedDataThumbnail->width);
        static::assertSame(720, $embedDataThumbnail->height);
        static::assertSame(null, $embedData->idKey);

        $embedDataThumbnailSized = $embedData->getThumbnailSized(640, 480);

        static::assertStringEndsWith('/1582080456-a2e326a6c36aa95042a2549ada80e8507061768e4aec00c887cd6531b5cfb499-d_640x480.jpg', $embedDataThumbnailSized->url);
        static::assertSame(640, $embedDataThumbnailSized->width);
        static::assertSame(480, $embedDataThumbnailSized->height);

        $embedDataThumbnailSizedWithoutHeight = $embedData->getThumbnailSized(640);

        static::assertStringEndsWith('/1582080456-a2e326a6c36aa95042a2549ada80e8507061768e4aec00c887cd6531b5cfb499-d_640.jpg', $embedDataThumbnailSizedWithoutHeight->url);
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
        $this->expectExceptionMessage('No user credentials were provided.');

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
            ->fromUrl('https://vimeo.com/273356233/83d37231b0');

        static::assertTrue($embedData->found);
        static::assertSame('Commencement Address 2018: David Sedaris', $embedData->title);
        static::assertStringContainsString('David Sedaris delivers the commencement address at Oberlin College on  Monday, May 28, 2018.', $embedData->description);
        static::assertIsArray($embedData->tags);
        static::assertSame('https://vimeo.com/273356233', $embedData->url);

        $embedDataThumbnail = $embedData->getThumbnail();

        static::assertStringEndsWith('/705132911_100x75.jpg', $embedDataThumbnail->url);

        $embedData = Embed::create([ 'vimeo.accessToken' => $vimeoAccessToken ])
            ->fromUrl('https://player.vimeo.com/video/1');

        static::assertFalse($embedData->found);
        static::assertNull($embedData->title);
        static::assertSame('https://vimeo.com/1', $embedData->url);
    }
}
