<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverPool;

/**
 * Class ReadFactoryTest
 */
class ReadFactoryTest extends \PHPUnit_Framework_TestCase
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
        $factory = new ReadFactory($driverPool);
        $result = $factory->create('path', $protocol, $driver);
        $this->assertInstanceOf('Magento\Framework\Filesystem\File\Read', $result);
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
        $factory = new ReadFactory(new DriverPool());
        $factory->create('path');
    }
}
