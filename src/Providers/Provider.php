<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Providers;

use Rentalhost\Vanilla\Embed\EmbedData;

abstract class Provider
{
    abstract public static function extractEmbedData(string $normalizedUrl): EmbedData;

    abstract public static function isUrlCompatible(string $normalizedUrl): bool;
}
