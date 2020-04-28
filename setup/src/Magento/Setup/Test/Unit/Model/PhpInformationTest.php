<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\PhpInformation;
use PHPUnit\Framework\TestCase;

/**
 * Tests Magento\Setup\Model\PhpInformation
 */
class PhpInformationTest extends TestCase
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
        $this->assertIsArray($actualExtensions);

        // Calling second type should cause class variable to be used
        $this->assertSame($actualExtensions, $phpInformation->getCurrent());
        $this->assertAttributeNotEmpty('current', $phpInformation);
    }
}
