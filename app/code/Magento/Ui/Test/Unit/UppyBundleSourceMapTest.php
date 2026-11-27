<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Ensures the shipped Uppy bundle does not reference a missing source map.
 */
class UppyBundleSourceMapTest extends TestCase
{
    public function testUppyMinJsDoesNotReferenceMissingSourceMap(): void
    {
        $root = defined('BP') ? BP : dirname(__DIR__, 6);
        $path = $root . '/lib/web/jquery/uppy/dist/uppy.min.js';

        $this->assertFileExists($path);

        $contents = file_get_contents($path);
        $this->assertNotFalse($contents);
        $this->assertStringNotContainsString(
            'sourceMappingURL=uppy.min.js.map',
            $contents,
            'uppy.min.js must not reference uppy.min.js.map when the map file is not shipped'
        );
    }
}
