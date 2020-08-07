<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Test\Unit\File;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Filesystem\File\WriteFactory;
use PHPUnit\Framework\TestCase;

class WriteFactoryTest extends TestCase
{
    public function testCreate()
    {
        $driverPool = $this->createPartialMock(DriverPool::class, ['getDriver']);
        $driverPool->expects($this->never())->method('getDriver');
        $factory = new WriteFactory($driverPool);
        $driver = $this->getMockForAbstractClass(DriverInterface::class);
        $driver->expects($this->any())->method('isExists')->willReturn(true);
        $result = $factory->create('path', $driver);
        $this->assertInstanceOf(Write::class, $result);
    }

    public function testCreateWithDriverCode()
    {
        $driverPool = $this->createPartialMock(DriverPool::class, ['getDriver']);
        $driverMock = $this->getMockForAbstractClass(DriverInterface::class);
        $driverMock->expects($this->any())->method('isExists')->willReturn(true);
        $driverPool->expects($this->once())->method('getDriver')->willReturn($driverMock);
        $factory = new WriteFactory($driverPool);
        $result = $factory->create('path', 'driverCode');
        $this->assertInstanceOf(Write::class, $result);
    }

    public function testCreateWithMode()
    {
        $driverPool = $this->createPartialMock(DriverPool::class, ['getDriver']);
        $driverPool->expects($this->never())->method('getDriver');
        $driver = $this->getMockForAbstractClass(DriverInterface::class);
        $driver->expects($this->any())->method('isExists')->willReturn(true);
        $factory = new WriteFactory($driverPool);
        $result = $factory->create('path', $driver, 'a+');
        $this->assertInstanceOf(Write::class, $result);
    }
}
