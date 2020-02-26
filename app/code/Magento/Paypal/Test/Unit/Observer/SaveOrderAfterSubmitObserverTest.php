<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Paypal\Observer\SaveOrderAfterSubmitObserver;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Paypal\Observer\SaveOrderAfterSubmitObserver
 */
class SaveOrderAfterSubmitObserverTest extends TestCase
{
    /*
     * Stub event key order
     */
    private const STUB_EVENT_KEY_ORDER = 'order';

    /**
     * Testable Object
     *
     * @var SaveOrderAfterSubmitObserver
     */
    private $observer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var Registry|MockObject
     */
    private $coreRegistryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreRegistryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['register'])
            ->getMock();

        $this->observer = $this->objectManager->getObject(
            SaveOrderAfterSubmitObserver::class,
            [
                'coreRegistry' => $this->coreRegistryMock
            ]
        );
    }

    /**
     * Test for execute(), covers test case to save order into registry
     */
    public function testExecuteSaveOrderIntoRegistry(): void
    {
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getData')
            ->with(self::STUB_EVENT_KEY_ORDER)
            ->willReturn($this->orderMock);

        $this->coreRegistryMock
            ->expects($this->once())
            ->method('register')
            ->with('hss_order', $this->orderMock, true);

        $this->observer->execute($this->observerMock);
    }
}
