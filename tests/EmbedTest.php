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
                    'found'    => true,
                    'title'    => 'Luis Fonsi - Despacito ft. Daddy Yankee',
                    'url'      => 'https://youtu.be/kJQP7kiw5Fk',
                    'id'       => 'kJQP7kiw5Fk'
                ]
            ],
            [
                // Recoverable non-normalized Url.
                'youtube.com/watch?v=kJQP7kiw5Fk',
                [
                    'provider' => 'youtube',
                    'found'    => true,
                    'id'       => 'kJQP7kiw5Fk'
                ]
            ],
            [
                'youtube.com/watch?v=aaaaaaaaaaa',
                [
                    'provider' => 'youtube',
                    'found'    => false,
                    'id'       => 'aaaaaaaaaaa',
                    'url'      => 'https://youtu.be/aaaaaaaaaaa',
                    'urlEmbed' => null
                ]
            ],
            [
                'https://vimeo.com/29950141',
                [
                    'provider' => 'vimeo',
                    'found'    => true,
                    'title'    => 'Landscapes: Volume Two',
                    'url'      => 'https://vimeo.com/29950141',
                    'urlEmbed' => 'https://player.vimeo.com/video/29950141?app_id=122963',
                    'id'       => '29950141'
                ]
            ],
            [
                'https://vimeo.com/344997253/ab1b6f2867',
                [
                    'provider'    => 'vimeo',
                    'found'       => true,
                    'title'       => 'CAP Roundtable 2019.06.26',
                    'description' => 'CAP Roundtable 06/26/2019',
                    'tags'        => [ 'CAP Roundtable' ],
                    'url'         => 'https://vimeo.com/344997253/ab1b6f2867',
                    'urlEmbed'    => 'https://player.vimeo.com/video/344997253?app_id=122963',
                    'id'          => '344997253'
                ]
            ],
            [
                'https://player.vimeo.com/video/344997253',
                [
                    'provider'    => 'vimeo',
                    'found'       => true,
                    'title'       => 'CAP Roundtable 2019.06.26',
                    'description' => 'CAP Roundtable 06/26/2019',
                    'tags'        => [ 'CAP Roundtable' ],
                    'url'         => 'https://vimeo.com/344997253',
                    'urlEmbed'    => 'https://player.vimeo.com/video/344997253?app_id=122963',
                    'id'          => '344997253'
                ]
            ],
            [
                'https://vimeo.com/1',
                [
                    'provider' => 'vimeo',
                    'found'    => false,
                    'url'      => 'https://vimeo.com/1',
                    'id'       => '1',
                    'idKey'    => null
                ]
            ],
            [
                'https://vimeo.com/1/a',
                [
                    'provider' => 'vimeo',
                    'found'    => false,
                    'url'      => 'https://vimeo.com/1/a',
                    'id'       => '1',
                    'idKey'    => 'a'
                ]
            ],
            [
                'https://soundcloud.com/david-rodrigues-277280782/impact-moderato-1',
                [
                    'provider'    => 'soundcloud',
                    'found'       => true,
                    'title'       => 'Impact Moderato (Public)',
                    'url'         => 'https://soundcloud.com/david-rodrigues-277280782/impact-moderato-1',
                    'urlEmbed'    => 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/898962922',
                    'id'          => 'david-rodrigues-277280782/impact-moderato-1',
                    'tags'        => [ 'Cinematic', 'test' ],
                    'trackId'     => 898962922,
                    'trackUser'   => 'david-rodrigues-277280782',
                    'trackName'   => 'impact-moderato-1',
                    'trackSecret' => null
                ]
            ],
            [
                'https://soundcloud.com/a/b',
                [
                    'provider'    => 'soundcloud',
                    'found'       => false,
                    'url'         => 'https://soundcloud.com/a/b',
                    'id'          => 'a/b',
                    'trackId'     => null,
                    'trackUser'   => 'a',
                    'trackName'   => 'b',
                    'trackSecret' => null
                ]
            ],
            [
                'https://soundcloud.com/david-rodrigues-277280782/impact-moderato/s-MjcQ5BtcRPp',
                [
                    'provider'    => 'soundcloud',
                    'found'       => true,
                    'title'       => 'Impact Moderato (Unlisted)',
                    'url'         => 'https://soundcloud.com/david-rodrigues-277280782/impact-moderato/s-MjcQ5BtcRPp',
                    'urlEmbed'    => 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/898937494%3Fsecret_token%3Ds-MjcQ5BtcRPp',
                    'id'          => 'david-rodrigues-277280782/impact-moderato',
                    'tags'        => [ 'Cinematic' ],
                    'trackId'     => 898937494,
                    'trackUser'   => 'david-rodrigues-277280782',
                    'trackName'   => 'impact-moderato',
                    'trackSecret' => 's-MjcQ5BtcRPp'
                ]
            ],
            [
                'https://soundcloud.com/a/b/c',
                [
                    'provider'    => 'soundcloud',
                    'found'       => false,
                    'url'         => 'https://soundcloud.com/a/b/c',
                    'id'          => 'a/b',
                    'trackId'     => null,
                    'trackUser'   => 'a',
                    'trackName'   => 'b',
                    'trackSecret' => 'c'
                ]
            ],
            [
                'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/898937494%3Fsecret_token%3Ds-MjcQ5BtcRPp',
                [
                    'provider'    => 'soundcloud',
                    'found'       => true,
                    'title'       => 'Impact Moderato (Unlisted)',
                    'url'         => 'https://soundcloud.com/david-rodrigues-277280782/impact-moderato/s-MjcQ5BtcRPp',
                    'urlEmbed'    => 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/898937494%3Fsecret_token%3Ds-MjcQ5BtcRPp',
                    'id'          => 'david-rodrigues-277280782/impact-moderato',
                    'trackId'     => 898937494,
                    'trackUser'   => 'david-rodrigues-277280782',
                    'trackName'   => 'impact-moderato',
                    'trackSecret' => 's-MjcQ5BtcRPp'
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
