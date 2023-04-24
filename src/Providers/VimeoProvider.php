<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Providers;

use JsonException;
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

        if (preg_match('~^(?:/video)?/(?<id>\d+)(?<key>/[\da-f]+)?(?:/|$)~', $postNormalizedUrlPath, $postNormalizedUrlPathMatch)) {
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
        catch (JsonException) {
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

        return (bool) preg_match('~^\d+(?:/[\da-f]+)?$~', $id);
    }

    private static function normalizeUrl(string $url): string
    {
        return preg_match('~\.jpg$~', $url)
            ? $url
            : $url . '.jpg';
    }

    public static function isUrlCompatible(string $normalizedUrl): bool
    {
        return self::isValidId(self::extractVideoId($normalizedUrl));
    }

    public static function extractEmbedData(Embed $embed, string $normalizedUrl): EmbedData
    {
        [ $videoId, $videoKey ] = array_pad(explode('/', self::extractVideoId($normalizedUrl), 2), 2, null);

        $vimeoAccessToken = $embed->getOption('vimeo.accessToken');

        $videoProperties = [];
        $videoThumbnails = [];

        if ($vimeoAccessToken) {
            $videoUrlContents = UrlSupport::getContents('https://api.vimeo.com/videos/' . $videoId, null, [
                'Authorization' => 'bearer ' . $vimeoAccessToken,
            ]);

            if ($videoUrlContents) {
                $responseJson = json_decode($videoUrlContents, true, 512, JSON_THROW_ON_ERROR);

                if (!array_key_exists('error', $responseJson)) {
                    $videoUrl = $responseJson['link'];

                    $videoProperties['title']       = $responseJson['name'] ?? null;
                    $videoProperties['description'] = $responseJson['description'] ?? null;
                    $videoProperties['tags']        = $responseJson['tags'] ?? null;

                    $videoThumbnails['default'] = [
                        'url'    => self::normalizeUrl(substr($responseJson['pictures']['sizes'][0]['link'], 0, -6)),
                        'width'  => (int) $responseJson['pictures']['sizes'][0]['width'],
                        'height' => (int) $responseJson['pictures']['sizes'][0]['height'],
                    ];
                }
            }
        }

        if (!$videoProperties) {
            $videoUrl      = 'https://vimeo.com/' . $videoId . ($videoKey ? '/' . $videoKey : null);
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
                    'url'      => $videoUrl,
                ]);
            }

            $videoMetasExtracted = MetaSupport::extractMetas($videoContents);

            if (empty($videoMetasExtracted['og:title'])) {
                return VimeoEmbedData::withAttributes([
                    'provider' => 'vimeo',
                    'found'    => false,
                    'id'       => $videoId,
                    'idKey'    => $videoKey,
                    'url'      => $videoUrl,
                ]);
            }

            $videoProperties['title']       = $videoMetasExtracted['og:title'];
            $videoProperties['description'] = $videoMetasExtracted['og:description'];
            $videoProperties['tags']        = $videoMetasExtracted['video:tag:array'] ??
                                              (!empty($videoMetasExtracted['video:tag']) ? [ $videoMetasExtracted['video:tag'] ] : []);

            $videoImage = parse_url($videoMetasExtracted['og:image'], PHP_URL_QUERY);

            if ($videoImage === null) {
                $videoThumbnails['default'] = [
                    'url'    => self::normalizeUrl(sprintf('%s_%sx%s',
                        $videoMetasExtracted['og:image'],
                        $videoMetasExtracted['og:image:width'],
                        $videoMetasExtracted['og:image:height'])),
                    'width'  => (int) $videoMetasExtracted['og:image:width'],
                    'height' => (int) $videoMetasExtracted['og:image:height'],
                ];
            }
            else {
                parse_str($videoImage, $videoThumbnailQuerystring);

                if (preg_match('~(?<width>\d+)x(?<height>\d+)~', $videoThumbnailQuerystring['src0'] ?? '', $videoThumbnailMatch)) {
                    $videoThumbnails['default'] = [
                        'url'    => self::normalizeUrl($videoThumbnailQuerystring['src0']),
                        'width'  => (int) $videoThumbnailMatch['width'],
                        'height' => (int) $videoThumbnailMatch['height'],
                    ];
                }
            }
        }

        return VimeoEmbedData::withAttributes(array_merge([
            'provider'   => 'vimeo',
            'found'      => true,
            'id'         => $videoId,
            'idKey'      => $videoKey,
            'url'        => $videoUrl ?? null,
            'urlEmbed'   => 'https://player.vimeo.com/video/' . $videoId,
            'thumbnails' => $videoThumbnails,
        ], $videoProperties));
    }
}
