<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\EmbedData;

class EmbedDataTest
    extends TestCase
{
    public function testGetSuggestedThumbnail(): void
    {
        $suggestedUrl = 'https://.../suggested.jpg';
        $embedData    = EmbedData::withAttributes([ 'thumbnails' => [ EmbedData::SUGGESTED_THUMBNAIL => $suggestedUrl ] ]);

        static::assertSame($suggestedUrl, $embedData->getSuggestedThumbnail());
    }

    public function testGetUrlData(): void
    {
        $embedData = EmbedData::withAttributes([ 'urlEmbed' => 'https://www.youtube.com/embed/kJQP7kiw5Fk' ]);

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
        $embedData           = EmbedData::withAttributes([]);
        $embedData->readonly = 'invalid';
    }
}
