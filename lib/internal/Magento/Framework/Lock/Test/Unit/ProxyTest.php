<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Test\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\Lock\Proxy;
use Magento\Framework\Lock\LockBackendFactory;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * @inheritdoc
 */
class ProxyTest extends TestCase
{
    /**
     * @var LockBackendFactory|MockObject
     */
    private $factoryMock;

    /**
     * @var LockManagerInterface|MockObject
     */
    private $lockerMock;

    /**
     * @var Proxy
     */
    private $proxy;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->factoryMock = $this->createMock(LockBackendFactory::class);
        $this->lockerMock = $this->getMockForAbstractClass(LockManagerInterface::class);
        $this->proxy = new Proxy($this->factoryMock);
    }

    /**
     * @return void
     */
    public function testIsLocked()
    {
        $lockName = 'testLock';
        $this->factoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->lockerMock);
        $this->lockerMock->expects($this->exactly(2))
            ->method('isLocked')
            ->with($lockName)
            ->willReturn(true);

        $this->assertTrue($this->proxy->isLocked($lockName));

        // Call one more time to check that method Factory::create is called one time
        $this->assertTrue($this->proxy->isLocked($lockName));
    }

    /**
     * @return void
     */
    public function testLock()
    {
        $lockName = 'testLock';
        $timeout = 123;
        $this->factoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->lockerMock);
        $this->lockerMock->expects($this->exactly(2))
            ->method('lock')
            ->with($lockName, $timeout)
            ->willReturn(true);

        $this->assertTrue($this->proxy->lock($lockName, $timeout));

        // Call one more time to check that method Factory::create is called one time
        $this->assertTrue($this->proxy->lock($lockName, $timeout));
    }

    /**
     * @return void
     */
    public function testUnlock()
    {
        $lockName = 'testLock';
        $this->factoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->lockerMock);
        $this->lockerMock->expects($this->exactly(2))
            ->method('unlock')
            ->with($lockName)
            ->willReturn(true);

        $this->assertTrue($this->proxy->unlock($lockName));

        // Call one more time to check that method Factory::create is called one time
        $this->assertTrue($this->proxy->unlock($lockName));
    }
}
