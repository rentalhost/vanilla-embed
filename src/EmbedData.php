<?php

declare(strict_types=1);

namespace Rentalhost\Vanilla\Embed;

use Rentalhost\Vanilla\Embed\Providers\Data\ThumbnailData;

/**
 * @property-read string      $provider
 * @property-read bool        $found
 *
 * @property-read string      $id
 *
 * @property-read string      $title
 * @property-read string|null $description
 * @property-read string[]    $tags
 *
 * @property-read array[]     $thumbnails
 *
 * @property-read string      $url
 * @property-read string      $urlEmbed
 */
class EmbedData
{
    protected array $attributes = [];

    private array $preferredThumbnailOrder = [];

    public static function withAttributes(array $attributes): self
    {
        $embedData             = new static();
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

    public function getHtml(array|null $urlAttributes = null, array|null $htmlAttributes = null): string
    {
        $urlEmbed       = $this->urlEmbed . ($urlAttributes ? '?' . http_build_query($urlAttributes) : null);
        $htmlAttributes = [ 'frameborder' => 0, ...(array) $htmlAttributes ];

        return sprintf(/** @lang text */ '<iframe src="%s" %s></iframe>', $urlEmbed,
            implode(' ', array_map(static fn(string $attributeKey, $attributeValue) => sprintf('%s="%s"',
                htmlspecialchars($attributeKey, ENT_QUOTES | ENT_HTML5),
                htmlspecialchars((string) $attributeValue, ENT_QUOTES | ENT_HTML5)), array_keys($htmlAttributes), $htmlAttributes))
        );
    }

    public function getThumbnail(string|null $name = null): ThumbnailData|null
    {
        if (!$this->thumbnails) {
            return null;
        }

        if (!$name) {
            foreach ($this->preferredThumbnailOrder as $preferredThumbnailOrder) {
                if (array_key_exists($preferredThumbnailOrder, $this->thumbnails)) {
                    $name = $preferredThumbnailOrder;

                    break;
                }
            }
        }

        if (!$name) {
            $name = (string) array_key_last($this->thumbnails);
        }

        if ($name && array_key_exists($name, $this->thumbnails)) {
            $thumbnail = $this->thumbnails[$name];

            return ThumbnailData::create($thumbnail['url'], $thumbnail['width'] ?? null, $thumbnail['height'] ?? null);
        }

        return null;
    }

    public function setPreferredThumbnailOrder(array $preferredThumbnailOrder): self
    {
        $this->preferredThumbnailOrder = $preferredThumbnailOrder;

        return $this;
    }
}
