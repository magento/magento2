<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test;

class MemoryLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testPrintHeader()
    {
        $result = \Magento\TestFramework\MemoryLimit::printHeader();
        $this->assertNotEmpty($result);
        $this->assertStringEndsWith(PHP_EOL, $result);
    }

    public function testPrintStats()
    {
        $object = $this->_createObject(0, 0);
        $result = $object->printStats();
        $this->assertStringContainsString('Memory usage (OS):', $result);
        $this->assertStringContainsString('1.00M', $result);
        $this->assertStringContainsString('Estimated memory leak:', $result);
        $this->assertStringContainsString('reported by PHP', $result);
        $this->assertStringEndsWith(PHP_EOL, $result);

        $object = $this->_createObject('2M', 0);
        $this->assertStringContainsString('50.00% of configured 2.00M limit', $object->printStats());

        $object = $this->_createObject(0, '500K');
        $this->assertStringContainsString('% of configured 0.49M limit', $object->printStats());
    }

    public function testValidateUsage()
    {
        $object = $this->_createObject(0, 0);
        $this->assertNull($object->validateUsage());
    }

    public function testValidateUsageException()
    {
        $this->expectException(\LogicException::class);
        $object = $this->_createObject('500K', '2M');
        $object->validateUsage();
    }

    /**
     * @param string $memCap
     * @param string $leakCap
     * @return \Magento\TestFramework\MemoryLimit
     */
    protected function _createObject($memCap, $leakCap)
    {
        $helper = $this->createPartialMock(\Magento\TestFramework\Helper\Memory::class, ['getRealMemoryUsage']);
        $helper->expects($this->any())->method('getRealMemoryUsage')->will($this->returnValue(1024 * 1024));
        return new \Magento\TestFramework\MemoryLimit($memCap, $leakCap, $helper);
    }
}
