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
namespace Magento\Test\Helper;

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_shell;

    protected function setUp()
    {
        $this->_shell = $this->getMock('Magento\Framework\Shell', array('execute'), array(), '', false);
    }

    public function testGetRealMemoryUsageUnix()
    {
        $object = new \Magento\TestFramework\Helper\Memory($this->_shell);
        $this->_shell->expects(
            $this->at(0)
        )->method(
            'execute'
        )->with(
            $this->stringStartsWith('tasklist.exe ')
        )->will(
            $this->throwException(new \Magento\Framework\Exception('command not found'))
        );
        $this->_shell->expects(
            $this->at(1)
        )->method(
            'execute'
        )->with(
            $this->stringStartsWith('ps ')
        )->will(
            $this->returnValue('26321')
        );
        $this->assertEquals(26952704, $object->getRealMemoryUsage());
    }

    public function testGetRealMemoryUsageWin()
    {
        $this->_shell->expects(
            $this->once()
        )->method(
            'execute'
        )->with(
            $this->stringStartsWith('tasklist.exe ')
        )->will(
            $this->returnValue('"php.exe","12345","N/A","0","26,321 K"')
        );
        $object = new \Magento\TestFramework\Helper\Memory($this->_shell);
        $this->assertEquals(26952704, $object->getRealMemoryUsage());
    }

    /**
     * @param string $number
     * @param string $expected
     * @dataProvider convertToBytesDataProvider
     */
    public function testConvertToBytes($number, $expected)
    {
        $this->assertEquals($expected, \Magento\TestFramework\Helper\Memory::convertToBytes($number));
    }

    /**
     * @return array
     */
    public function convertToBytesDataProvider()
    {
        return array(
            'B' => array('1B', '1'),
            'KB' => array('3K', '3072'),
            'MB' => array('2M', '2097152'),
            'GB' => array('1G', '1073741824'),
            'regular spaces' => array('1 234 K', '1263616'),
            'no-break spaces' => array("1\xA0234\xA0K", '1263616'),
            'tab' => array("1\x09234\x09K", '1263616'),
            'coma' => array('1,234K', '1263616'),
            'dot' => array('1.234 K', '1263616')
        );
    }

    /**
     * @param string $number
     * @dataProvider convertToBytesBadFormatDataProvider
     * @expectedException \InvalidArgumentException
     */
    public function testConvertToBytesBadFormat($number)
    {
        \Magento\TestFramework\Helper\Memory::convertToBytes($number);
    }

    /**
     * @return array
     */
    public function convertToBytesBadFormatDataProvider()
    {
        return array(
            'more than one unit of measure' => array('1234KB'),
            'unknown unit of measure' => array('1234Z'),
            'non-integer value' => array('1,234.56 K')
        );
    }

    /**
     * @param string $number
     * @param string $expected
     * @dataProvider convertToBytes64DataProvider
     */
    public function testConvertToBytes64($number, $expected)
    {
        if (PHP_INT_SIZE <= 4) {
            $this->markTestSkipped('A 64-bit system is required to perform this test.');
        }
        $this->assertEquals($expected, \Magento\TestFramework\Helper\Memory::convertToBytes($number));
    }

    /**
     * @return array
     */
    public function convertToBytes64DataProvider()
    {
        return array(
            array('2T', '2199023255552'),
            array('1P', '1125899906842624'),
            array('2E', '2305843009213693952')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConvertToBytesInvalidArgument()
    {
        \Magento\TestFramework\Helper\Memory::convertToBytes('3Z');
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testConvertToBytesOutOfBounds()
    {
        if (PHP_INT_SIZE > 4) {
            $this->markTestSkipped('A 32-bit system is required to perform this test.');
        }
        \Magento\TestFramework\Helper\Memory::convertToBytes('2P');
    }
}
