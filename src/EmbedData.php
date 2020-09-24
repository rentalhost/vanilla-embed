<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed;

/**
 * @property-read string      $provider
 *
 * @property-read string      $id
 *
 * @property-read string      $title
 * @property-read string|null $description
 * @property-read string[]    $tags
 *
 * @property-read string[]    $thumbnails
 *
 * @property-read string      $url
 * @property-read string      $urlEmbed
 */
class EmbedData
{
    public const
        SUGGESTED_THUMBNAIL = 'suggested';

    private array $attributes = [];

    public static function withAttributes(array $attributes): self
    {
        $embedData             = new self;
        $embedData->attributes = $attributes;

        return $embedData;
    }

    /** @return mixed */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value)
    {
        throw new \RuntimeException('EmbedData is readonly');
    }

    public function __isset(string $name)
    {
        return isset($this->attributes[$name]);
    }

    public function getHtml(?array $urlAttributes = null, ?array $htmlAttributes = null): string
    {
        $urlEmbed       = $this->urlEmbed . ($urlAttributes ? '?' . http_build_query($urlAttributes) : null);
        $htmlAttributes = array_merge([ 'frameborder' => 0 ], (array) $htmlAttributes);

        return sprintf(/** @lang text */ '<iframe src="%s" %s></iframe>', $urlEmbed,
            implode(' ', array_map(static function (string $attributeKey, $attributeValue) {
                return sprintf('%s="%s"',
                    htmlspecialchars($attributeKey, ENT_QUOTES | ENT_HTML5),
                    htmlspecialchars((string) $attributeValue, ENT_QUOTES | ENT_HTML5));
            }, array_keys($htmlAttributes), $htmlAttributes))
        );
    }

    public function getSuggestedThumbnail(): ?string
    {
        return $this->thumbnails[self::SUGGESTED_THUMBNAIL] ?? null;
    }
}
