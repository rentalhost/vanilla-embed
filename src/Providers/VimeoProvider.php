<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Providers;

use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\EmbedData;
use Rentalhost\Vanilla\Embed\Providers\EmbedData\VimeoEmbedData;
use Rentalhost\Vanilla\Embed\Support\MetaSupport;
use Rentalhost\Vanilla\Embed\Support\UrlSupport;

class VimeoProvider
    extends Provider
{
    private static function extractVideoId(string $normalizedUrl): ?string
    {
        $postNormalizedUrl     = '//' . $normalizedUrl;
        $postNormalizedUrlHost = parse_url($postNormalizedUrl, PHP_URL_HOST);

        if ($postNormalizedUrlHost !== 'vimeo.com' &&
            $postNormalizedUrlHost !== 'player.vimeo.com') {
            return null;
        }

        $postNormalizedUrlPath = parse_url($postNormalizedUrl, PHP_URL_PATH);

        if (preg_match('~^(?:/video)?/(?<id>\d+)(?<key>/[0-9a-f]+)?(?:/|$)~', $postNormalizedUrlPath, $postNormalizedUrlPathMatch)) {
            return $postNormalizedUrlPathMatch['id'] . ($postNormalizedUrlPathMatch['key'] ?? null);
        }

        return null;
    }

    private static function isValidId(?string $id): bool
    {
        if (!$id) {
            return false;
        }

        return (bool) preg_match('~^\d+(?:/[0-9a-f]+)?$~', $id);
    }

    public static function isUrlCompatible(string $normalizedUrl): bool
    {
        return self::isValidId(self::extractVideoId($normalizedUrl));
    }

    public static function extractEmbedData(Embed $embed, string $normalizedUrl): EmbedData
    {
        [ $videoId, $videoKey ] = array_pad(explode('/', self::extractVideoId($normalizedUrl), 2), 2, null);

        $videoUrl        = 'https://vimeo.com/' . $videoId . ($videoKey ? '/' . $videoKey : null);
        $videoThumbnails = [];

        $videoContents = UrlSupport::getContents($videoUrl);

        if (!$videoContents) {
            return VimeoEmbedData::withAttributes([
                'provider' => 'vimeo',
                'found'    => false,
                'id'       => $videoId,
                'idKey'    => $videoKey,
                'url'      => $videoUrl
            ]);
        }

        $videoMetasExtracted = MetaSupport::extractMetasFromUrl($videoUrl);

        $videoProperties['title']       = $videoMetasExtracted['og:title'];
        $videoProperties['description'] = $videoMetasExtracted['og:description'];
        $videoProperties['tags']        = $videoMetasExtracted['video:tag:array'] ?? [];

        parse_str(parse_url($videoMetasExtracted['og:image'], PHP_URL_QUERY), $videoThumbnailQuerystring);

        if (preg_match('~(?<width>\d+)x(?<height>\d+)~', $videoThumbnailQuerystring['src0'] ?? '', $videoThumbnailMatch)) {
            $videoThumbnails['default'] = [
                'url'    => $videoThumbnailQuerystring['src0'],
                'width'  => (int) $videoThumbnailMatch['width'],
                'height' => (int) $videoThumbnailMatch['height']
            ];
        }

        return VimeoEmbedData::withAttributes(array_merge([
            'provider'   => 'vimeo',
            'found'      => true,
            'id'         => $videoId,
            'idKey'      => $videoKey,
            'url'        => $videoUrl,
            'urlEmbed'   => 'https://player.vimeo.com/video/' . $videoId . '?app_id=122963',
            'thumbnails' => $videoThumbnails
        ], $videoProperties));
    }
}
