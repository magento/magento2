<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Observer;

use Magento\Cms\Observer\NoRouteObserver;
use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NoRouteObserverTest extends TestCase
{
    /**
     * @var NoRouteObserver
     */
    protected $noRouteObserver;

    /**
     * @var Observer|MockObject
     */
    protected $observerMock;

    /**
     * @var Event|MockObject
     */
    protected $eventMock;

    /**
     * @var DataObject|MockObject
     */
    protected $objectMock;

    protected function setUp(): void
    {
        $this->observerMock = $this
            ->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this
            ->getMockBuilder(Event::class)
            ->addMethods(
                [
                    'getStatus',
                    'getRedirect',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this
            ->getMockBuilder(DataObject::class)
            ->addMethods(
                [
                    'setLoaded',
                    'setForwardModule',
                    'setForwardController',
                    'setForwardAction',
                    'setRedirectUrl',
                    'setRedirect',
                    'setPath',
                    'setArguments',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->noRouteObserver = $objectManager->getObject(
            NoRouteObserver::class,
            []
        );
    }

    /**
     * @covers \Magento\Cms\Observer\NoRouteObserver::execute
     */
    public function testNoRoute()
    {
        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getStatus')
            ->willReturn($this->objectMock);
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setLoaded')
            ->with(true)
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setForwardModule')
            ->with('cms')
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setForwardController')
            ->with('index')
            ->willReturnSelf();
        $this->objectMock
            ->expects($this->atLeastOnce())
            ->method('setForwardAction')
            ->with('noroute')
            ->willReturnSelf();

        $this->assertEquals($this->noRouteObserver, $this->noRouteObserver->execute($this->observerMock));
    }
}
