<?php

declare(strict_types=1);

namespace Rentalhost\Vanilla\Embed\Providers\Data;

class ThumbnailData
{
    public int|null $height;

    public string $url;

    public int|null $width;

    private function __construct()
    {
    }

    public static function create(string $url, int|null $width, int|null $height): self
    {
        $thumbnailData         = new static();
        $thumbnailData->url    = $url;
        $thumbnailData->width  = $width;
        $thumbnailData->height = $height;

        return $thumbnailData;
    }
}
