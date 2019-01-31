<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Event\Test\Unit;

use \Magento\Framework\Event\ObserverFactory;

/**
 * Class ConfigTest
 *
 * @package Magento\Framework\Event
 */
class ObserverFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ObserverFactory
     */
    protected $observerFactory;

    protected function setUp()
    {
        $this->objectManagerMock = $this->createPartialMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['get', 'create']
        );
        $this->observerFactory = new ObserverFactory($this->objectManagerMock);
    }

    public function testGet()
    {
        $className = 'Magento\Class';
        $observerMock = $this->getMockBuilder('Observer')->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($className)
            ->will($this->returnValue($observerMock));

        $result = $this->observerFactory->get($className);
        $this->assertEquals($observerMock, $result);
    }

    public function testCreate()
    {
        $className = 'Magento\Class';
        $observerMock =  $this->getMockBuilder('Observer')->getMock();
        $arguments = ['arg1', 'arg2'];

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $this->equalTo($arguments))
            ->will($this->returnValue($observerMock));

        $result = $this->observerFactory->create($className, $arguments);
        $this->assertEquals($observerMock, $result);
    }
}
