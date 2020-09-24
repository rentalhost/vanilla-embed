<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Providers;

use GuzzleHttp\Client as GuzzleClient;
use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\EmbedData;
use Rentalhost\Vanilla\Embed\Support\MetaSupport;

class YoutubeProvider
    extends Provider
{
    private const
        ID_REGEXP = '[\w-]{11}',
        ALLOWED_HOSTS = [ 'youtube.com', 'youtu.be' ];

    private static function extractVideoId(string $normalizedUrl): ?string
    {
        $postNormalizedUrl     = '//' . self::postNormalizeUrl($normalizedUrl);
        $postNormalizedUrlHost = parse_url($postNormalizedUrl, PHP_URL_HOST);

        if (!in_array($postNormalizedUrlHost, self::ALLOWED_HOSTS, true)) {
            return null;
        }

        $postNormalizedUrlPath = rtrim(parse_url($postNormalizedUrl, PHP_URL_PATH), '/');

        if ($postNormalizedUrlHost === 'youtu.be') {
            return substr($postNormalizedUrlPath, 1);
        }

        if ($postNormalizedUrlPath === '/watch') {
            parse_str((string) parse_url($postNormalizedUrl, PHP_URL_QUERY), $postNormalizedUrlQuerystring);

            return $postNormalizedUrlQuerystring['v'] ?? null;
        }

        if (preg_match(sprintf('~^/embed/(?<id>%s)(/|$)~', self::ID_REGEXP), $postNormalizedUrlPath, $postNormalizedUrlPathMatch)) {
            return $postNormalizedUrlPathMatch['id'];
        }

        return null;
    }

    private static function isValidId(?string $id): bool
    {
        if (!$id) {
            return false;
        }

        return (bool) preg_match('~^' . self::ID_REGEXP . '$~', $id);
    }

    private static function postNormalizeUrl(string $normalizedUrl): string
    {
        return preg_replace('~^m\.~', null, $normalizedUrl);
    }

    public static function isUrlCompatible(string $normalizedUrl): bool
    {
        return self::isValidId(self::extractVideoId($normalizedUrl));
    }

    public static function extractEmbedData(Embed $embed, string $normalizedUrl): EmbedData
    {
        $videoId            = self::extractVideoId($normalizedUrl);
        $videoUrl           = 'https://youtu.be/' . $videoId;
        $videoThumbnailBase = 'https://i.ytimg.com/vi/' . $videoId;
        $videoMetas         = null;

        $googleKey = $embed->getOption('google.key');

        if ($googleKey) {
            $guzzleResponse = (new GuzzleClient)->get('https://www.googleapis.com/youtube/v3/videos', [
                'query' => [
                    'key'  => $googleKey,
                    'id'   => $videoId,
                    'part' => 'snippet'
                ]
            ]);

            $guzzleResponseJson = json_decode($guzzleResponse->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $videoMetas['title']              = $guzzleResponseJson['items'][0]['snippet']['title'] ?? null;
            $videoMetas['description']        = $guzzleResponseJson['items'][0]['snippet']['description'] ?? null;
            $videoMetas['og:video:tag:array'] = $guzzleResponseJson['items'][0]['snippet']['tags'] ?? null;
        }

        if (!$videoMetas) {
            $videoMetas = MetaSupport::extractMetasFromUrl($videoUrl);
        }

        return EmbedData::withAttributes([
            'provider' => 'youtube',

            'id' => $videoId,

            'title'       => $videoMetas['title'],
            'description' => $videoMetas['description'],
            'tags'        => $videoMetas['og:video:tag:array'],

            'thumbnails' => [
                // https://stackoverflow.com/a/20542029/755393
                // Resolution 120x90 (guaranteed).
                'default'                      => $videoThumbnailBase . '/default.jpg',
                '1'                            => $videoThumbnailBase . '/1.jpg',
                '2'                            => $videoThumbnailBase . '/2.jpg',
                '3'                            => $videoThumbnailBase . '/3.jpg',

                // Resolution 320x180 (guaranteed).
                'mqdefault'                    => $videoThumbnailBase . '/mqdefault.jpg',
                'mq1'                          => $videoThumbnailBase . '/mq1.jpg',
                'mq2'                          => $videoThumbnailBase . '/mq2.jpg',
                'mq3'                          => $videoThumbnailBase . '/mq3.jpg',

                // Resolution 480x360 (guaranteed).
                'hqdefault'                    => $videoThumbnailBase . '/hqdefault.jpg',
                'hq1'                          => $videoThumbnailBase . '/hq1.jpg',
                'hq2'                          => $videoThumbnailBase . '/hq2.jpg',
                'hq3'                          => $videoThumbnailBase . '/hq3.jpg',
                '0'                            => $videoThumbnailBase . '/0.jpg',

                // Resolution 640x480.
                'sddefault'                    => $videoThumbnailBase . '/sddefault.jpg',
                'sd1'                          => $videoThumbnailBase . '/sd1.jpg',
                'sd2'                          => $videoThumbnailBase . '/sd2.jpg',
                'sd3'                          => $videoThumbnailBase . '/sd3.jpg',

                // Resolution 1280x720.
                'hq720'                        => $videoThumbnailBase . '/hq720.jpg',

                // Max resolution available.
                'maxresdefault'                => $videoThumbnailBase . '/maxresdefault.jpg',

                // Suggested resolution.
                EmbedData::SUGGESTED_THUMBNAIL => $videoThumbnailBase . '/mqdefault.jpg',
            ],

            'url'      => $videoUrl,
            'urlEmbed' => 'https://youtube.com/embed/' . $videoId
        ]);
    }
}
