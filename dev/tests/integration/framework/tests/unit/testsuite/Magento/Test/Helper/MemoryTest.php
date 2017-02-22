<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->_shell = $this->getMock('Magento\Framework\Shell', ['execute'], [], '', false);
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
            $this->throwException(new \Magento\Framework\Exception\LocalizedException(__('command not found')))
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
        return [
            'B' => ['1B', '1'],
            'KB' => ['3K', '3072'],
            'MB' => ['2M', '2097152'],
            'GB' => ['1G', '1073741824'],
            'regular spaces' => ['1 234 K', '1263616'],
            'no-break spaces' => ["1\xA0234\xA0K", '1263616'],
            'tab' => ["1\x09234\x09K", '1263616'],
            'coma' => ['1,234K', '1263616'],
            'dot' => ['1.234 K', '1263616']
        ];
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
        return [
            'more than one unit of measure' => ['1234KB'],
            'unknown unit of measure' => ['1234Z'],
            'non-integer value' => ['1,234.56 K']
        ];
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
        return [
            ['2T', '2199023255552'],
            ['1P', '1125899906842624'],
            ['2E', '2305843009213693952']
        ];
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
