# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.7] - 2021-05-12

### Added

- `VimeoProvider` will appends `.jpg` automatically to thumbnails, since *vimeo.com* can now return the extension occasionally, and not always;

## [0.1.6] - 2021-04-01

### Fixed

- `VimeoProvider` and `YoutubeProvider` as the *not found* page does not is a 404 Error, so workaround it by checking if title is available;

## [0.1.5] - 2020-10-20

### Fixed

- `YoutubeProvider` fix on reading a video with empty tags;

## [0.1.4] - 2020-10-19

### Added

- `VimeoProvider` now supports Vimeo API by passing `vimeo.accessToken` option, so it will be used as default request method and previous methods will be used only as fallback when it fails or is not defined;

### Fixed

- `UrlSupport` will not thrown an exception for `HTTP 429 Too Many Requests` error, so providers will just set `$embedData->found` to `false` (**important:** authenticate to provider to avoid that it to happens with an existing content);

## [0.1.3] - 2020-10-16

### Changed

- `VimeoProvider` do not appends `app_id` anymore because it have no side-effects;

### Fixed

- `UrlSupport` will not thrown an exception for `HTTP 403 Forbidden` error, so providers will just set `$embedData->found` to `false`;

## [0.1.2] - 2020-10-16

### Added

- `VimeoProvider` now supports a new way to get video metadata as fallback to default method;

## [0.1.1] - 2020-10-16

### Added

- `EmbedData` now provides the property `found` that indicates if the requested content was found;

## [0.1.0] - 2020-09-24

### Added

- Initial version, supporting Youtube, Vimeo and Soundcloud;
- `YoutubeProvider` have support to `google.key` option to use Google API directly;

[0.1.7]: https://github.com/rentalhost/vanilla-embed/compare/0.1.6..0.1.7

[0.1.6]: https://github.com/rentalhost/vanilla-embed/compare/0.1.5..0.1.6

[0.1.5]: https://github.com/rentalhost/vanilla-embed/compare/0.1.4..0.1.5

[0.1.4]: https://github.com/rentalhost/vanilla-embed/compare/0.1.3..0.1.4

[0.1.3]: https://github.com/rentalhost/vanilla-embed/compare/0.1.2..0.1.3

[0.1.2]: https://github.com/rentalhost/vanilla-embed/compare/0.1.1..0.1.2

[0.1.1]: https://github.com/rentalhost/vanilla-embed/compare/0.1.0..0.1.1

[0.1.0]: https://github.com/rentalhost/vanilla-embed/tree/0.1.0
