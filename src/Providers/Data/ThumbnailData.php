<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Providers\Data;

class ThumbnailData
{
    public ?int $height;

    public string $url;

    public ?int $width;

    private function __construct()
    {
    }

    public static function create(string $url, ?int $width, ?int $height): self
    {
        $thumbnailData         = new static;
        $thumbnailData->url    = $url;
        $thumbnailData->width  = $width;
        $thumbnailData->height = $height;

        return $thumbnailData;
    }
}
