<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

        // Class variable 'current' should be empty the first time
        $this->assertAttributeEmpty('current', $phpInformation);
        $actualExtensions = $phpInformation->getCurrent();
        $this->assertTrue(is_array($actualExtensions));

        // Calling second type should cause class variable to be used
        $this->assertSame($actualExtensions, $phpInformation->getCurrent());
        $this->assertAttributeNotEmpty('current', $phpInformation);
    }
}
