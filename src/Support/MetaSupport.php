<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Support;

use Symfony\Component\DomCrawler\Crawler;

class MetaSupport
{
    private static function extractNormalizedMetas(string $contents, string $metaXPath, array $metaNames, string $metaContent): array
    {
        $crawlerMetas = [];

        $crawler = new Crawler($contents);
        $crawler->filterXPath($metaXPath)->each(static function (Crawler $node) use (&$crawlerMetas, $metaNames, $metaContent) {
            $nodeName = null;

            foreach ($metaNames as $metaName) {
                $nodeName = $node->attr($metaName);

                if ($nodeName) {
                    break;
                }
            }

            if ($nodeName) {
                $crawlerMetas[$nodeName][] = $node->attr($metaContent);
            }
        });

        $normalizedMetas = [];

        foreach ($crawlerMetas as $crawlerMetaKey => $crawlerMetaValue) {
            if (count($crawlerMetaValue) >= 2) {
                $crawlerMetaValueUnique = array_unique($crawlerMetaValue);

                if (count($crawlerMetaValueUnique) >= 2) {
                    $normalizedMetas[$crawlerMetaKey]            = $crawlerMetaValue[array_key_last($crawlerMetaValue)];
                    $normalizedMetas[$crawlerMetaKey . ':array'] = $crawlerMetaValueUnique;

                    continue;
                }
            }

            $normalizedMetas[$crawlerMetaKey] = $crawlerMetaValue[0];
        }

        return $normalizedMetas;
    }

    public static function extractLinks(string $contents): array
    {
        return self::extractNormalizedMetas($contents, '//link[@rel]', [ 'rel' ], 'href');
    }

    public static function extractLinksFromUrl(string $url): array
    {
        return self::extractLinks((string) UrlSupport::getContents($url));
    }

    public static function extractMetas(string $contents): array
    {
        return self::extractNormalizedMetas($contents, '//meta[@content]', [ 'name', 'property', 'itemprop' ], 'content');
    }

    public static function extractMetasFromUrl(string $url): array
    {
        return self::extractMetas((string) UrlSupport::getContents($url));
    }
}
