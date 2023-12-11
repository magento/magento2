<?php
/**
 * Collection of various useful functions
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function testGetTrimmedPhpVersion()
    {
        $util = new Util();
        $version = implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);
        $this->assertEquals($version, $util->getTrimmedPhpVersion());
    }
}
