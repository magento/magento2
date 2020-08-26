<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\Framework\Filesystem\Test\Unit;

use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;
use PHPUnit\Framework\TestCase;

class DriverPoolTest extends TestCase
{
    public function testGetDriver()
    {
        $object = new DriverPool();
        foreach ([DriverPool::FILE, DriverPool::HTTP, DriverPool::HTTPS, DriverPool::ZLIB] as $code) {
            $this->assertInstanceOf(DriverInterface::class, $object->getDriver($code));
        }
        $default = $object->getDriver('');
        $this->assertInstanceOf(File::class, $default);
        $this->assertSame($default, $object->getDriver(''));
    }

    public function testCustomDriver()
    {
        $customOne = $this->getMockForAbstractClass(DriverInterface::class);
        $customTwo = get_class($this->getMockForAbstractClass(DriverInterface::class));
        $object = new DriverPool(['customOne' => $customOne, 'customTwo' => $customTwo]);
        $this->assertSame($customOne, $object->getDriver('customOne'));
        $this->assertInstanceOf(DriverInterface::class, $object->getDriver('customOne'));
        $this->assertInstanceOf($customTwo, $object->getDriver('customTwo'));
        $this->assertInstanceOf(DriverInterface::class, $object->getDriver('customTwo'));
    }

    public function testCustomDriverException()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The specified type \'stdClass\' does not implement DriverInterface.');
        new DriverPool(['custom' => new \StdClass()]);
    }
}
