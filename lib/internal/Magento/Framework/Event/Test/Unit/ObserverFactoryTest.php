<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit;

use Magento\Framework\Event\ObserverFactory;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ObserverFactoryTest extends TestCase
{
    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ObserverFactory
     */
    protected $observerFactory;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createPartialMock(
            ObjectManager::class,
            ['get', 'create']
        );
        $this->observerFactory = new ObserverFactory($this->objectManagerMock);
    }

    public function testGet()
    {
        $className = 'Magento\Class';
        $observerMock = $this->getMockBuilder('Observer')
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($className)
            ->willReturn($observerMock);

        $result = $this->observerFactory->get($className);
        $this->assertEquals($observerMock, $result);
    }

    public function testCreate()
    {
        $className = 'Magento\Class';
        $observerMock =  $this->getMockBuilder('Observer')
            ->getMock();
        $arguments = ['arg1', 'arg2'];

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $arguments)
            ->willReturn($observerMock);

        $result = $this->observerFactory->create($className, $arguments);
        $this->assertEquals($observerMock, $result);
    }
}
