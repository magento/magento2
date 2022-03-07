<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin\Model\AdminOrder;

use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\AdminOrder\Create as Create;
use Magento\Sales\Plugin\Model\AdminOrder\GuestNameUpdate;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class GuestNameUpdateTest extends TestCase
{
    /**
     * @var GuestNameUpdate
     */
    private $guestNameUpdate;

    /**
     * @var SessionQuote|MockObject
     */
    private $sessionQuote;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->sessionQuote = $this->getMockBuilder(SessionQuote::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getOrder',
                    'getStore',
                ]
            )
            ->getMock();

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->sessionQuote->method('getStore')
            ->willReturn($storeMock);

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getCustomerFirstname',
                    'getCustomerLastname',
                    'getCustomerMiddlename',
                    'save',
                ]
            )
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->guestNameUpdate = $objectManagerHelper->getObject(
            GuestNameUpdate::class,
            [
                'quoteSession' => $this->sessionQuote,
            ]
        );
    }

    public function testAfterCreateOrder()
    {
        /** @var $subject Create */
        $subject = $this->createMock(Create::class);

        $this->orderMock->method('getId')
            ->willReturn(1);
        $this->orderMock->method('getCustomerFirstname')
            ->willReturn('firstname');
        $this->orderMock->method('getCustomerLastname')
            ->willReturn('lastname');
        $this->orderMock->method('getCustomerMiddlename')
            ->willReturn('middlename');
        $this->orderMock->method('save')
            ->willReturnSelf();

        $this->sessionQuote->method('getOrder')
            ->willReturn($this->orderMock);

        $guestOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getCustomerIsGuest',
                    'save'
                ]
            )
            ->getMock();
        $guestOrder->method('getId')
            ->willReturn(2);
        $guestOrder->method('getCustomerIsGuest')
            ->willReturn(true);

        $updatedOrder = $this->guestNameUpdate->afterCreateOrder($subject, $guestOrder);

        self::assertEquals($this->orderMock->getCustomerFirstname(), $updatedOrder->getCustomerFirstname());
    }
}
