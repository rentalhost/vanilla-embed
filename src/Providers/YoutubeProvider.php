<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Providers;

use Rentalhost\Vanilla\Embed\Embed;
use Rentalhost\Vanilla\Embed\EmbedData;
use Rentalhost\Vanilla\Embed\Support\MetaSupport;
use Rentalhost\Vanilla\Embed\Support\UrlSupport;

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
        $videoProperties    = [];
        $videoThumbnails    = [];

        $googleKey = $embed->getOption('google.key');

        if ($googleKey) {
            $responseJson = json_decode(UrlSupport::getContents('https://www.googleapis.com/youtube/v3/videos', [
                'key'  => $googleKey,
                'id'   => $videoId,
                'part' => 'snippet'
            ]), true, 512, JSON_THROW_ON_ERROR);

            $videoProperties['title']       = $responseJson['items'][0]['snippet']['title'] ?? null;
            $videoProperties['description'] = $responseJson['items'][0]['snippet']['description'] ?? null;
            $videoProperties['tags']        = $responseJson['items'][0]['snippet']['tags'] ?? null;

            $videoThumbnails = $responseJson['items'][0]['snippet']['thumbnails'] ?? [];
        }

        if (!$videoProperties) {
            $videoMetasExtracted = MetaSupport::extractMetasFromUrl($videoUrl);

            $videoProperties['title']       = $videoMetasExtracted['title'];
            $videoProperties['description'] = $videoMetasExtracted['description'];
            $videoProperties['tags']        = $videoMetasExtracted['og:video:tag:array'];

            $videoThumbnails = [
                'default' => [ 'url' => $videoThumbnailBase . '/default.jpg', 'width' => 120, 'height' => 90 ],
                'medium'  => [ 'url' => $videoThumbnailBase . '/mqdefault.jpg', 'width' => 320, 'height' => 180 ],
                'high'    => [ 'url' => $videoThumbnailBase . '/hqdefault.jpg', 'width' => 480, 'height' => 360 ]
            ];
        }

        return EmbedData::withAttributes(array_merge([
            'provider'   => 'youtube',
            'id'         => $videoId,
            'url'        => $videoUrl,
            'urlEmbed'   => 'https://youtube.com/embed/' . $videoId,
            'thumbnails' => $videoThumbnails
        ], $videoProperties))->setPreferredThumbnailOrder([ 'maxres', 'medium' ]);
    }
}
