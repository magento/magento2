<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverPool;

/**
 * Class WriteFactoryTest
 */
class WriteFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string|null $protocol
     * @param \PHPUnit_Framework_MockObject_MockObject|null $driver
     * @dataProvider createProvider
     */
    public function testCreate($protocol, $driver)
    {
        $driverPool = $this->getMock('Magento\Framework\Filesystem\DriverPool', ['getDriver']);
        if ($protocol) {
            $driverMock = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
            $driverMock->expects($this->any())->method('isExists')->willReturn(true);
            $driverPool->expects($this->once())->method('getDriver')->willReturn($driverMock);
        } else {
            $driverPool->expects($this->never())->method('getDriver');
        }
        $factory = new WriteFactory($driverPool);
        $result = $factory->create('path', $protocol, $driver);
        $this->assertInstanceOf('Magento\Framework\Filesystem\File\Write', $result);
    }

    /**
     * @return array
     */
    public function createProvider()
    {
        $driver = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $driver->expects($this->any())->method('isExists')->willReturn(true);
        return [
            [null, $driver],
            ['custom_protocol', null]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateException()
    {
        $factory = new WriteFactory(new DriverPool());
        $factory->create('path');
    }

    public function testCreateWithMode()
    {
        $driver = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $driver->expects($this->any())->method('isExists')->willReturn(false);
        $driverPool = $this->getMock('Magento\Framework\Filesystem\DriverPool', ['getDriver']);
        $driverPool->expects($this->once())->method('getDriver')->with('protocol')->willReturn($driver);
        $factory = new WriteFactory($driverPool);
        $result = $factory->create('path', 'protocol', null, 'a+');
        $this->assertInstanceOf('Magento\Framework\Filesystem\File\Write', $result);
    }
}
