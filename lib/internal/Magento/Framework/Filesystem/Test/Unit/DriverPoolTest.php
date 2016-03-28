<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Filesystem\Test\Unit;

use \Magento\Framework\Filesystem\DriverPool;

class DriverPoolTest extends \PHPUnit_Framework_TestCase
{
    public function testGetDriver()
    {
        $object = new DriverPool();
        foreach ([DriverPool::FILE, DriverPool::HTTP, DriverPool::HTTPS, DriverPool::ZLIB] as $code) {
            $this->assertInstanceOf('\Magento\Framework\Filesystem\DriverInterface', $object->getDriver($code));
        }
        $default = $object->getDriver('');
        $this->assertInstanceOf('\Magento\Framework\Filesystem\Driver\File', $default);
        $this->assertSame($default, $object->getDriver(''));
    }

    public function testCustomDriver()
    {
        $customOne = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\DriverInterface');
        $customTwo = get_class($this->getMockForAbstractClass('\Magento\Framework\Filesystem\DriverInterface'));
        $object = new DriverPool(['customOne' => $customOne, 'customTwo' => $customTwo]);
        $this->assertSame($customOne, $object->getDriver('customOne'));
        $this->assertInstanceOf('\Magento\Framework\Filesystem\DriverInterface', $object->getDriver('customOne'));
        $this->assertEquals($customTwo, get_class($object->getDriver('customTwo')));
        $this->assertInstanceOf('\Magento\Framework\Filesystem\DriverInterface', $object->getDriver('customTwo'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The specified type 'stdClass' does not implement DriverInterface.
     */
    public function testCustomDriverException()
    {
        new DriverPool(['custom' => new \StdClass()]);
    }
}
