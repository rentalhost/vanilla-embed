<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Providers\EmbedData;

use Rentalhost\Vanilla\Embed\EmbedData;
use Rentalhost\Vanilla\Embed\Providers\Data\ThumbnailData;

/**
 * @property-read string|null $idKey
 */
class VimeoEmbedData
    extends EmbedData
{
    public function getThumbnailSized(int $width, int $height): ThumbnailData
    {
        $thumbnailUrl = $this->attributes['thumbnails']['default']['url'];

        return ThumbnailData::create(preg_replace('~\d+x\d+~', $width . 'x' . $height, $thumbnailUrl), $width, $height);
    }
}
