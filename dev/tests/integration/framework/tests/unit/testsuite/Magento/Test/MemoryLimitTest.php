<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test;

class MemoryLimitTest extends \PHPUnit_Framework_TestCase
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
        $this->assertContains('Memory usage (OS):', $result);
        $this->assertContains('1.00M', $result);
        $this->assertContains('Estimated memory leak:', $result);
        $this->assertContains('reported by PHP', $result);
        $this->assertStringEndsWith(PHP_EOL, $result);

        $object = $this->_createObject('2M', 0);
        $this->assertContains('50.00% of configured 2.00M limit', $object->printStats());

        $object = $this->_createObject(0, '500K');
        $this->assertContains('% of configured 0.49M limit', $object->printStats());
    }

    public function testValidateUsage()
    {
        $object = $this->_createObject(0, 0);
        $this->assertNull($object->validateUsage());
    }

    /**
     * @expectedException \LogicException
     */
    public function testValidateUsageException()
    {
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
        $helper = $this->getMock(
            'Magento\TestFramework\Helper\Memory',
            array('getRealMemoryUsage'),
            array(),
            '',
            false
        );
        $helper->expects($this->any())->method('getRealMemoryUsage')->will($this->returnValue(1024 * 1024));
        return new \Magento\TestFramework\MemoryLimit($memCap, $leakCap, $helper);
    }
}
