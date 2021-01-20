<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Observer;

class NoRouteObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Cms\Observer\NoRouteObserver
     */
    protected $noRouteObserver;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \Magento\Framework\Event|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventMock;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectMock;

    protected function setUp(): void
    {
        $this->observerMock = $this
            ->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventMock = $this
            ->getMockBuilder(\Magento\Framework\Event::class)
            ->setMethods(
                [
                    'getStatus',
                    'getRedirect',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectMock = $this
            ->getMockBuilder(\Magento\Framework\DataObject::class)
            ->setMethods(
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

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->noRouteObserver = $objectManager->getObject(
            \Magento\Cms\Observer\NoRouteObserver::class,
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
