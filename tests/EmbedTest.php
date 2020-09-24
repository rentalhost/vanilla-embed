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
                'https://youtube.com/watch?v=kJQP7kiw5Fk',
                [
                    'provider' => 'youtube',
                    'title'    => 'Luis Fonsi - Despacito ft. Daddy Yankee',
                    'url'      => 'https://youtu.be/kJQP7kiw5Fk',
                    'id'       => 'kJQP7kiw5Fk'
                ]
            ],
            [
                // Recoverable non-normalized Url.
                'youtube.com/watch?v=kJQP7kiw5Fk',
                [ 'id' => 'kJQP7kiw5Fk' ]
            ],
            [
                'https://vimeo.com/29950141',
                [
                    'provider' => 'vimeo',
                    'title'    => 'Landscapes: Volume Two',
                    'url'      => 'https://vimeo.com/29950141',
                    'urlEmbed' => 'https://player.vimeo.com/video/29950141?app_id=122963',
                    'id'       => '29950141'
                ]
            ],
            [
                'https://vimeo.com/344997253/ab1b6f2867',
                [
                    'title'    => 'CAP Roundtable 2019.06.26',
                    'url'      => 'https://vimeo.com/344997253/ab1b6f2867',
                    'urlEmbed' => 'https://player.vimeo.com/video/344997253?app_id=122963',
                    'id'       => '344997253'
                ]
            ]
        ];
    }

    /** @dataProvider dataProviderFromUrl */
    public function testFromUrl(string $url, array $exceptedAttributes): void
    {
        $embedData = Embed::create()->fromUrl($url);

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

        Embed::create()->fromUrl('invalid.url');
    }

    public function testInvalidUrlExceptionForEmptyUrl(): void
    {
        $this->expectException(InvalidUrlException::class);
        $this->expectExceptionMessage('Url is empty');

        Embed::create()->fromUrl(null);
    }

    public function testProviderNotImplementedException(): void
    {
        $this->expectException(ProviderNotImplementedException::class);
        $this->expectExceptionMessage('provider for not.implemented yet not implemented');

        Embed::create()->fromUrl('not.implemented/');
    }
}
