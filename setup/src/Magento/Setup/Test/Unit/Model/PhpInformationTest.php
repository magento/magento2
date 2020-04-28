<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

        $actualExtensions = $phpInformation->getCurrent();
        $this->assertIsArray($actualExtensions);

        // Calling second type should cause class variable to be used
        $this->assertSame($actualExtensions, $phpInformation->getCurrent());
    }
}
