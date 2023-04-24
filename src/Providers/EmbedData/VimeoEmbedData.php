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
    public function getThumbnailSized(int $width, int|null $height = null): ThumbnailData
    {
        $thumbnailUrl = $this->attributes['thumbnails']['default']['url'];

        if (!$height) {
            return ThumbnailData::create(preg_replace('~\d+x\d+~', (string) $width, $thumbnailUrl), $width, null);
        }

        return ThumbnailData::create(preg_replace('~\d+x\d+~', $width . 'x' . $height, $thumbnailUrl), $width, $height);
    }
}
