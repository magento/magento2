<?php
/**
 * Collection of various useful functions
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Test\Unit;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTrimmedPhpVersion()
    {
        $util = new \Magento\Framework\Util();
        $version = implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);
        $this->assertEquals($version, $util->getTrimmedPhpVersion());
    }
}
