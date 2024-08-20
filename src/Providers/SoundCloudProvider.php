<?php

declare(strict_types=1);

namespace Rentalhost\Vanilla\Embed\Providers;

use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\EmbedData;
use Rentalhost\Vanilla\Embed\Providers\EmbedData\SoundCloudEmbedData;
use Rentalhost\Vanilla\Embed\Support\MetaSupport;
use Rentalhost\Vanilla\Embed\Support\UrlSupport;

class SoundCloudProvider
    extends Provider
{
    private const THUMBNAIL_SIZES = [
        't500x500' => 500,
        'crop'     => 400,
        't300x300' => 300,
        'large'    => 100,
        't67x67'   => 67,
        'badge'    => 47,
        'small'    => 32,
        'tiny'     => 18,
        'mini'     => 16,
    ];

    public static function extractEmbedData(Embed $embed, string $normalizedUrl): EmbedData
    {
        [ $trackUser, $trackName, $trackSecret ] = array_pad(explode('/', self::extractTrackId($normalizedUrl), 3), 3, null);

        $trackId         = null;
        $trackUrl        = 'https://soundcloud.com/' . $trackUser . '/' . $trackName . ($trackSecret ? '/' . $trackSecret : null);
        $trackThumbnails = [];

        $trackUrlContents = UrlSupport::getContents($trackUrl);

        if ($trackUrlContents) {
            $trackMetasExtracted = MetaSupport::extractMetas($trackUrlContents);

            if (isset($trackMetasExtracted['og:title'])) {
                if (preg_match('~soundcloud:tracks:(?<trackId>\d+)~', $trackUrlContents, $trackUrlContentsMatch)) {
                    $trackId = (int) $trackUrlContentsMatch['trackId'];
                }

                $trackProperties                = [];
                $trackProperties['title']       = $trackMetasExtracted['og:title'];
                $trackProperties['description'] = $trackMetasExtracted['og:description'];

                if (preg_match('~"tag_list":"(?<tags>[^"]+)"~', $trackUrlContents, $trackUrlContentsMatch)) {
                    $trackProperties['tags'] = explode(' ', $trackUrlContentsMatch['tags']);
                }

                foreach (self::THUMBNAIL_SIZES as $thumbnailName => $thumbnailSize) {
                    $trackThumbnails[$thumbnailName] = [
                        'url'    => preg_replace('~(-)t500x500(\.)~', '$1' . $thumbnailName . '$2', $trackMetasExtracted['og:image']),
                        'width'  => $thumbnailSize,
                        'height' => $thumbnailSize,
                    ];
                }

                return SoundCloudEmbedData::withAttributes([
                    'provider'    => 'soundcloud',
                    'found'       => true,
                    'id'          => $trackUser . '/' . $trackName,
                    'trackId'     => $trackId,
                    'trackUser'   => $trackUser,
                    'trackName'   => $trackName,
                    'trackSecret' => $trackSecret,
                    'url'         => $trackUrl,
                    'urlEmbed'    => 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/' . $trackId .
                                     ($trackSecret ? urlencode('?secret_token=' . $trackSecret) : null),
                    'thumbnails'  => $trackThumbnails,
                    ...$trackProperties,
                ])->setPreferredThumbnailOrder([ 't500x500' ]);
            }
        }

        return SoundCloudEmbedData::withAttributes([
            'provider'    => 'soundcloud',
            'found'       => false,
            'id'          => $trackUser . '/' . $trackName,
            'trackId'     => $trackId,
            'trackUser'   => $trackUser,
            'trackName'   => $trackName,
            'trackSecret' => $trackSecret,
            'url'         => $trackUrl,
        ]);
    }

    public static function isUrlCompatible(string $normalizedUrl): bool
    {
        return self::isValidId(self::extractTrackId($normalizedUrl));
    }

    private static function extractTrackId(string $normalizedUrl): string|null
    {
        $postNormalizedUrl     = '//' . $normalizedUrl;
        $postNormalizedUrlHost = parse_url($postNormalizedUrl, PHP_URL_HOST);

        if ($postNormalizedUrlHost === 'soundcloud.com') {
            $postNormalizedUrlPath = parse_url($postNormalizedUrl, PHP_URL_PATH);

            if (preg_match('~^/(?<user>[^/]+)(?<trackName>/[^/]+)(?<trackSecret>/[^/]+)?~', $postNormalizedUrlPath, $postNormalizedUrlPathMatch)) {
                return $postNormalizedUrlPathMatch['user'] . $postNormalizedUrlPathMatch['trackName'] .
                       ($postNormalizedUrlPathMatch['trackSecret'] ?? null);
            }
        }

        if ($postNormalizedUrlHost === 'w.soundcloud.com') {
            $postNormalizedLinks = MetaSupport::extractLinksFromUrl($postNormalizedUrl);

            if ($postNormalizedLinks) {
                return self::extractTrackId(substr($postNormalizedLinks['canonical'], 8));
            }
        }

        return null;
    }

    private static function isValidId(string|null $id): bool
    {
        if (!$id) {
            return false;
        }

        return (bool) preg_match('~^[^/]+/[^/]+(/[^/]+)?~', $id);
    }
}
