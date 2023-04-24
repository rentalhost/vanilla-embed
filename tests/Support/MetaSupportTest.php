<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Embed\Tests\Support;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Embed\Support\MetaSupport;

class MetaSupportTest
    extends TestCase
{
    public static function testExtractMetas(): void
    {
        static::assertSame([
            'metaName'        => 'ok',
            'metaItemprop'    => 'ok',
            'metaProperty'    => 'ok',
            'metaDuplicated'  => 'ok',
            'metaArray'       => 'okLastItem',
            'metaArray:array' => [ 'okFirstItem', 'okLastItem' ],
        ], MetaSupport::extractMetas(
            '<meta name="metaName" content="ok" />' .
            '<meta itemprop="metaItemprop" content="ok" />' .
            '<meta property="metaProperty" content="ok" />' .

            '<meta name="metaDuplicated" content="ok" />' .
            '<meta name="metaDuplicated" content="ok" />' .

            '<meta name="metaArray" content="okFirstItem" />' .
            '<meta name="metaArray" content="okLastItem" />' .
            '<meta name="metaArray" content="okLastItem" />'
        ));
    }
}
