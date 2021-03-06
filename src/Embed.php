<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed;

use Rentalhost\Vanilla\Embed\Exceptions\InvalidUrlException;
use Rentalhost\Vanilla\Embed\Exceptions\ProviderNotImplementedException;
use Rentalhost\Vanilla\Embed\Providers\Provider;
use Rentalhost\Vanilla\Embed\Providers\SoundCloudProvider;
use Rentalhost\Vanilla\Embed\Providers\VimeoProvider;
use Rentalhost\Vanilla\Embed\Providers\YoutubeProvider;

class Embed
{
    private const EMBED_PROVIDERS = [
        YoutubeProvider::class,
        VimeoProvider::class,
        SoundCloudProvider::class
    ];

    private array $options;

    private function __construct()
    {
    }

    private static function normalizeUrl(string $url): ?string
    {
        if (preg_match('~^https?://(?:www\.)?(.+)~', $url, $urlMatch)) {
            return $urlMatch[1];
        }

        if (preg_match('~^\w+\.\w+/~', $url)) {
            return $url;
        }

        return null;
    }

    public static function create(?array $options = null): self
    {
        $embed          = new static;
        $embed->options = $options ?? [];

        return $embed;
    }

    public function fromUrl(?string $url): EmbedData
    {
        if (!$url) {
            throw new InvalidUrlException('Url is empty');
        }

        $normalizedUrl = self::normalizeUrl($url);

        if (!$normalizedUrl) {
            throw new InvalidUrlException('could not parse Url');
        }

        /** @var Provider|string $embedProviderClass */
        foreach (self::EMBED_PROVIDERS as $embedProviderClass) {
            if ($embedProviderClass::isUrlCompatible($normalizedUrl)) {
                return $embedProviderClass::extractEmbedData($this, $normalizedUrl);
            }
        }

        throw new ProviderNotImplementedException(
            sprintf('provider for %s yet not implemented', parse_url('//' . $normalizedUrl, PHP_URL_HOST))
        );
    }

    /** @return mixed */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }
}
