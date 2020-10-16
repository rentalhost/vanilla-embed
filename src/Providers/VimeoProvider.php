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

    private static function getFallbackContents(string $videoId, ?string &$videoKey): ?string
    {
        $videoUrl      = 'https://player.vimeo.com/video/' . $videoId;
        $videoContents = UrlSupport::getContents($videoUrl);

        if (!$videoContents) {
            return null;
        }

        $videoMetasOffset = strpos($videoContents, 'var config = ') + 13;
        $videoMetaSubstr  = substr($videoContents, $videoMetasOffset, strpos($videoContents, '; if (!config.request)') - $videoMetasOffset);

        if (!$videoMetaSubstr) {
            return null;
        }

        try {
            $videoMetasExtracted = json_decode($videoMetaSubstr, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (\JsonException $exception) {
            return null;
        }

        $videoKey = $videoMetasExtracted['video']['unlisted_hash'];

        return UrlSupport::getContents('https://vimeo.com/' . $videoId . '/' . $videoKey);
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
            $videoContents = self::getFallbackContents($videoId, $videoKey);
        }

        if (!$videoContents) {
            return VimeoEmbedData::withAttributes([
                'provider' => 'vimeo',
                'found'    => false,
                'id'       => $videoId,
                'idKey'    => $videoKey,
                'url'      => $videoUrl
            ]);
        }

        $videoMetasExtracted = MetaSupport::extractMetas($videoContents);

        $videoProperties['title']       = $videoMetasExtracted['og:title'];
        $videoProperties['description'] = $videoMetasExtracted['og:description'];
        $videoProperties['tags']        = $videoMetasExtracted['video:tag:array'] ??
                                          (!empty($videoMetasExtracted['video:tag']) ? [ $videoMetasExtracted['video:tag'] ] : []);

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
            'urlEmbed'   => 'https://player.vimeo.com/video/' . $videoId,
            'thumbnails' => $videoThumbnails
        ], $videoProperties));
    }
}
