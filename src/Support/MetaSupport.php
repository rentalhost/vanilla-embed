<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Support;

use Symfony\Component\DomCrawler\Crawler;

class MetaSupport
{
    public static function extractMetas(string $contents): array
    {
        $crawler      = new Crawler($contents);
        $crawlerMetas = [];

        $crawler->filterXPath('//meta[@content]')->each(static function (Crawler $node) use (&$crawlerMetas) {
            $nodeName = $node->attr('name') ?: $node->attr('property') ?: $node->attr('itemprop');

            if ($nodeName) {
                $crawlerMetas[$nodeName][] = $node->attr('content');
            }
        });

        $outputMetas = [];

        foreach ($crawlerMetas as $crawlerMetaKey => $crawlerMetaValue) {
            if (count($crawlerMetaValue) >= 2) {
                $crawlerMetaValueUnique = array_unique($crawlerMetaValue);

                if (count($crawlerMetaValueUnique) >= 2) {
                    $outputMetas[$crawlerMetaKey]            = $crawlerMetaValue[array_key_last($crawlerMetaValue)];
                    $outputMetas[$crawlerMetaKey . ':array'] = $crawlerMetaValueUnique;

                    continue;
                }
            }

            $outputMetas[$crawlerMetaKey] = $crawlerMetaValue[0];
        }

        return $outputMetas;
    }

    public static function extractMetasFromUrl(string $url): array
    {
        return self::extractMetas(file_get_contents($url));
    }
}
