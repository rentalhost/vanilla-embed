<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\Exceptions\InvalidUrlException;
use Rentalhost\Vanilla\Embed\Exceptions\ProviderNotImplementedException;

class EmbedTest
    extends TestCase
{
    public function dataProviderFromUrl(): array
    {
        return [
            [
                'https://www.youtube.com/watch?v=kJQP7kiw5Fk',
                [
                    'title' => 'Luis Fonsi - Despacito ft. Daddy Yankee',
                    'url'   => 'https://youtu.be/kJQP7kiw5Fk',
                    'id'    => 'kJQP7kiw5Fk'
                ]
            ],
            [
                // Recoverable non-normalized Url.
                'youtube.com/watch?v=kJQP7kiw5Fk',
                [ 'id' => 'kJQP7kiw5Fk' ]
            ]
        ];
    }

    /** @dataProvider dataProviderFromUrl */
    public function testFromUrl(string $url, array $exceptedAttributes): void
    {
        $embedData = (new Embed)->fromUrl($url);

        foreach ($exceptedAttributes as $exceptedAttributeKey => $exceptedAttributeValue) {
            static::assertSame(
                $exceptedAttributeValue,
                $embedData->{$exceptedAttributeKey},
                sprintf('[%s]->%s', $url, $exceptedAttributeKey)
            );
        }
    }

    public function testInvalidUrlException(): void
    {
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage('could not parse Url');

        (new Embed)->fromUrl('invalid.url');
    }

    public function testInvalidUrlExceptionForEmptyUrl(): void
    {
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage('Url is empty');

        (new Embed)->fromUrl(null);
    }

    public function testProviderNotImplementedException(): void
    {
        $this->expectException(ProviderNotImplementedException::class);
        $this->expectExceptionMessage('provider for not.implemented yet not implemented');

        (new Embed)->fromUrl('not.implemented/');
    }
}
