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
        SoundCloudProvider::class,
    ];

    private array $options;

    private function __construct()
    {
    }

    private static function normalizeUrl(string $url): string|null
    {
        if (preg_match('~^https?://(?:www\.)?(.+)~', $url, $urlMatch)) {
            return $urlMatch[1];
        }

        if (preg_match('~^\w+\.\w+/~', $url)) {
            return $url;
        }

        return null;
    }

    public static function create(array|null $options = null): self
    {
        $embed          = new static();
        $embed->options = $options ?? [];

        return $embed;
    }

    public function fromUrl(string|null $url): EmbedData
    {
        if (!$url) {
            throw new InvalidUrlException('Url is empty');
        }

        $normalizedUrl = self::normalizeUrl($url);

        if (!$normalizedUrl) {
            throw new InvalidUrlException('could not parse Url');
        }

        /** @var Provider[] $embedProviders */
        $embedProviders = self::EMBED_PROVIDERS;

        foreach ($embedProviders as $embedProviderClass) {
            if ($embedProviderClass::isUrlCompatible($normalizedUrl)) {
                return $embedProviderClass::extractEmbedData($this, $normalizedUrl);
            }
        }

        throw new ProviderNotImplementedException(
            sprintf('provider for %s yet not implemented', parse_url('//' . $normalizedUrl, PHP_URL_HOST))
        );
    }

    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }
}
