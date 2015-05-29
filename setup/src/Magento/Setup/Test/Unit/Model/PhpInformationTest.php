<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\PhpInformation;

/**
 * Tests Magento\Setup\Model\PhpInformation
 */
class PhpInformationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRequiredMinimumXDebugNestedLevel()
    {
        $phpInformation = new PhpInformation();
        $this->assertEquals(200, $phpInformation->getRequiredMinimumXDebugNestedLevel());
    }

    public function testGetCurrent()
    {
        $phpInformation = new PhpInformation();
        $this->assertEquals(get_loaded_extensions(), $phpInformation->getCurrent());
    }
}
