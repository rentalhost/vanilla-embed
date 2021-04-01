<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\EmbedData;

class EmbedDataTest
    extends TestCase
{
    public function testGetThumbnailAsNull(): void
    {
        $embedData = EmbedData::withAttributes([]);

        static::assertNull($embedData->getThumbnail());
    }

    public function testGetThumbnailAsNullDueToInexistence(): void
    {
        $embedData = EmbedData::withAttributes([ 'thumbnails' => [ 'high' => [] ] ]);

        static::assertNull($embedData->getThumbnail('inexistent'));
    }

    public function testGetThumbnailBasedOnPreferredOrder(): void
    {
        $suggestedUrl = 'https://.../suggested.jpg';
        $embedData    = EmbedData::withAttributes([ 'thumbnails' => [ 'low' => [ 'url' => 'nope' ], 'high' => [ 'url' => $suggestedUrl ] ] ])
            ->setPreferredThumbnailOrder([ 'high' ]);

        static::assertSame($suggestedUrl, $embedData->getThumbnail()->url);
    }

    public function testGetThumbnailWithoutAPreferredOrder(): void
    {
        $suggestedUrl = 'https://.../suggested.jpg';
        $embedData    = EmbedData::withAttributes([ 'thumbnails' => [ 'low' => [ 'url' => 'nope' ], 'high' => [ 'url' => $suggestedUrl ] ] ]);

        static::assertSame($suggestedUrl, $embedData->getThumbnail()->url);
    }

    public function testGetUrlData(): void
    {
        $embedData = EmbedData::withAttributes([ 'urlEmbed' => 'https://www.youtube.com/embed/kJQP7kiw5Fk' ]);

        /** @noinspection HtmlDeprecatedAttribute */
        static::assertSame('<iframe src="https://www.youtube.com/embed/kJQP7kiw5Fk?loop=1" frameborder="0" width="100%" data-custom="&quot;"></iframe>', $embedData->getHtml(
            [ 'loop' => 1 ],
            [ 'width' => '100%', 'data-custom' => '"' ]
        ));
    }

    public function testIsset(): void
    {
        $embedData = EmbedData::withAttributes([ 'id' => '123' ]);

        static::assertTrue(isset($embedData->id));
        static::assertFalse(isset($embedData->title));
    }

    public function testProviderNotImplementedException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('EmbedData is readonly');

        /** @var \stdClass|EmbedData $embedData */
        $embedData = EmbedData::withAttributes([]);

        /** @noinspection PhpUndefinedFieldInspection */
        $embedData->readonly = 'invalid';
    }
}
